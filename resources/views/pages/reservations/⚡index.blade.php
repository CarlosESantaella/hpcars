<?php

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('Reservas')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function reservations()
    {
        return Reservation::query()
            ->with(['client', 'vehicle'])
            ->when($this->search, fn (Builder $query) => $query->where(function ($q) {
                $q->whereHas('client', fn ($clientQuery) => $clientQuery->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery->where('plate', 'like', "%{$this->search}%")
                        ->orWhere('brand', 'like', "%{$this->search}%")
                        ->orWhere('model', 'like', "%{$this->search}%"));
            }))
            ->when($this->status, fn (Builder $query) => $query->where('status', $this->status))
            ->orderByDesc('start_date')
            ->paginate(10);
    }

    public function delete(Reservation $reservation): void
    {
        $reservation->delete();
        $this->resetPage();
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Gestión de Reservas</flux:heading>
            <flux:button variant="primary" icon="plus" href="{{ route('reservations.create') }}" wire:navigate>
                Nueva Reserva
            </flux:button>
        </div>

        {{-- Filtros --}}
        <flux:card class="p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por cliente o vehículo..." icon="magnifying-glass" />
                </div>
                <div class="w-full sm:w-48">
                    <flux:select wire:model.live="status" placeholder="Todos los estados">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach(ReservationStatus::cases() as $statusOption)
                            <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </flux:card>

        {{-- Tabla --}}
        <flux:card>
            <flux:table :paginate="$this->reservations">
                <flux:table.columns>
                    <flux:table.column>Cliente</flux:table.column>
                    <flux:table.column>Vehículo</flux:table.column>
                    <flux:table.column>Fecha inicio</flux:table.column>
                    <flux:table.column>Fecha fin</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->reservations as $reservation)
                        <flux:table.row :key="$reservation->id">
                            <flux:table.cell class="font-medium">{{ $reservation->client->name }}</flux:table.cell>
                            <flux:table.cell>
                                <div>
                                    <flux:text>{{ $reservation->vehicle->fullName() }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500">{{ $reservation->vehicle->plate }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $reservation->start_date->format('d/m/Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $reservation->end_date->format('d/m/Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$reservation->status->color()" size="sm">
                                    {{ $reservation->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" :href="route('reservations.show', $reservation)" wire:navigate>
                                            Ver
                                        </flux:menu.item>
                                        <flux:menu.item icon="pencil" :href="route('reservations.edit', $reservation)" wire:navigate>
                                            Editar
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $reservation->id }})" wire:confirm="¿Estás seguro de eliminar esta reserva?">
                                            Eliminar
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center">
                                <flux:text class="text-zinc-500">No se encontraron reservas.</flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
</div>
