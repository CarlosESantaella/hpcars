<?php

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('Clientes')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function clients()
    {
        return Client::query()
            ->withCount('reservations')
            ->when($this->search, fn (Builder $query) => $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('dni', 'like', "%{$this->search}%");
            }))
            ->orderBy('name')
            ->paginate(10);
    }

    public function delete(Client $client): void
    {
        $client->delete();
        $this->resetPage();
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Gestión de Clientes</flux:heading>
            <flux:button variant="primary" icon="plus" href="{{ route('clients.create') }}" wire:navigate>
                Nuevo Cliente
            </flux:button>
        </div>

        {{-- Búsqueda --}}
        <flux:card class="p-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, email, teléfono o DNI..." icon="magnifying-glass" />
        </flux:card>

        {{-- Tabla --}}
        <flux:card>
            <flux:table :paginate="$this->clients">
                <flux:table.columns>
                    <flux:table.column>Nombre</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Teléfono</flux:table.column>
                    <flux:table.column>DNI</flux:table.column>
                    <flux:table.column>Alquileres</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->clients as $client)
                        <flux:table.row :key="$client->id">
                            <flux:table.cell class="font-medium">{{ $client->name }}</flux:table.cell>
                            <flux:table.cell>{{ $client->email ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $client->phone ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($client->dni)
                                    <flux:badge size="sm">{{ $client->dni }}</flux:badge>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ $client->reservations_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" :href="route('clients.show', $client)" wire:navigate>
                                            Ver
                                        </flux:menu.item>
                                        <flux:menu.item icon="pencil" :href="route('clients.edit', $client)" wire:navigate>
                                            Editar
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $client->id }})" wire:confirm="¿Estás seguro de eliminar este cliente?">
                                            Eliminar
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center">
                                <flux:text class="text-zinc-500">No se encontraron clientes.</flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
</div>
