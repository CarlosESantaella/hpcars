<?php

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('Vehículos')] class extends Component {
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
    public function vehicles()
    {
        return Vehicle::query()
            ->when($this->search, fn (Builder $query) => $query->where(function ($q) {
                $q->where('plate', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%")
                    ->orWhere('model', 'like', "%{$this->search}%");
            }))
            ->when($this->status, fn (Builder $query) => $query->where('status', $this->status))
            ->orderBy('plate')
            ->paginate(10);
    }

    public function delete(Vehicle $vehicle): void
    {
        $vehicle->delete();
        $this->resetPage();
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Gestión de Flota</flux:heading>
            <flux:button variant="primary" icon="plus" href="{{ route('vehicles.create') }}" wire:navigate>
                Nuevo Vehículo
            </flux:button>
        </div>

        {{-- Filtros --}}
        <flux:card class="p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por matrícula, marca o modelo..." icon="magnifying-glass" />
                </div>
                <div class="w-full sm:w-48">
                    <flux:select wire:model.live="status" placeholder="Todos los estados">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach(VehicleStatus::cases() as $statusOption)
                            <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </flux:card>

        {{-- Tabla --}}
        <flux:card>
            <flux:table :paginate="$this->vehicles">
                <flux:table.columns>
                    <flux:table.column>Matrícula</flux:table.column>
                    <flux:table.column>Modelo</flux:table.column>
                    <flux:table.column>Año</flux:table.column>
                    <flux:table.column>Kilometraje</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->vehicles as $vehicle)
                        <flux:table.row :key="$vehicle->id">
                            <flux:table.cell>
                                <flux:badge size="sm">{{ $vehicle->plate }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div>
                                    <flux:text class="font-medium">{{ $vehicle->fullName() }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500">{{ $vehicle->color }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $vehicle->year }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($vehicle->current_mileage, 0, ',', '.') }} km</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$vehicle->status->color()" size="sm">
                                    {{ $vehicle->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" :href="route('vehicles.show', $vehicle)" wire:navigate>
                                            Ver
                                        </flux:menu.item>
                                        <flux:menu.item icon="pencil" :href="route('vehicles.edit', $vehicle)" wire:navigate>
                                            Editar
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $vehicle->id }})" wire:confirm="¿Estás seguro de eliminar este vehículo?">
                                            Eliminar
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center">
                                <flux:text class="text-zinc-500">No se encontraron vehículos.</flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
</div>
