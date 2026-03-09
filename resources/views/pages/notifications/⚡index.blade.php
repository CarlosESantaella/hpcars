<?php

use App\Enums\NotificationType;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('Notificaciones')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function notifications()
    {
        return Notification::query()
            ->with('vehicle')
            ->when($this->search, fn (Builder $query) => $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%")
                    ->orWhereHas('vehicle', fn ($vq) => $vq->where('plate', 'like', "%{$this->search}%")
                        ->orWhere('brand', 'like', "%{$this->search}%")
                        ->orWhere('model', 'like', "%{$this->search}%"));
            }))
            ->when($this->type, fn (Builder $query) => $query->where('type', $this->type))
            ->when($this->status === 'read', fn (Builder $query) => $query->where('is_read', true))
            ->when($this->status === 'unread', fn (Builder $query) => $query->where('is_read', false))
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->update(['is_read' => true]);
        unset($this->notifications);
    }

    public function markAsUnread(Notification $notification): void
    {
        $notification->update(['is_read' => false]);
        unset($this->notifications);
    }

    public function markAllAsRead(): void
    {
        Notification::unread()->update(['is_read' => true]);
        unset($this->notifications);
    }

    public function deleteNotification(Notification $notification): void
    {
        $notification->delete();
        unset($this->notifications);
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Notificaciones</flux:heading>
        <flux:button variant="primary" wire:click="markAllAsRead" icon="check">
            Marcar todas como leídas
        </flux:button>
    </div>

    {{-- Filtros --}}
    <flux:card class="p-4">
        <div class="grid gap-4 sm:grid-cols-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por vehículo, mensaje..." icon="magnifying-glass" />
            <flux:select wire:model.live="type" placeholder="Todos los tipos">
                <flux:select.option value="">Todos los tipos</flux:select.option>
                @foreach(NotificationType::cases() as $notificationType)
                    <flux:select.option :value="$notificationType->value">{{ $notificationType->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="status" placeholder="Todos los estados">
                <flux:select.option value="">Todos</flux:select.option>
                <flux:select.option value="unread">No leídas</flux:select.option>
                <flux:select.option value="read">Leídas</flux:select.option>
            </flux:select>
        </div>
    </flux:card>

    {{-- Tabla --}}
    <flux:card>
        <flux:table :paginate="$this->notifications">
            <flux:table.columns>
                <flux:table.column>Tipo</flux:table.column>
                <flux:table.column>Vehículo</flux:table.column>
                <flux:table.column>Título</flux:table.column>
                <flux:table.column>Mensaje</flux:table.column>
                <flux:table.column>Fecha evento</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($this->notifications as $notification)
                    <flux:table.row :key="$notification->id">
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$notification->type->color()">
                                {{ $notification->type->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div>
                                <flux:text class="font-medium">{{ $notification->vehicle->fullName() }}</flux:text>
                                <flux:text class="text-xs text-zinc-500">{{ $notification->vehicle->plate }}</flux:text>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $notification->title }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-sm">{{ $notification->message }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>{{ $notification->due_date->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>
                            @if($notification->is_read)
                                <flux:badge size="sm" color="zinc">Leída</flux:badge>
                            @else
                                <flux:badge size="sm" color="red">No leída</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    @if($notification->is_read)
                                        <flux:menu.item icon="eye-slash" wire:click="markAsUnread({{ $notification->id }})">
                                            Marcar como no leída
                                        </flux:menu.item>
                                    @else
                                        <flux:menu.item icon="check" wire:click="markAsRead({{ $notification->id }})">
                                            Marcar como leída
                                        </flux:menu.item>
                                    @endif
                                    <flux:menu.item icon="trash" variant="danger" wire:click="deleteNotification({{ $notification->id }})" wire:confirm="¿Está seguro de eliminar esta notificación?">
                                        Eliminar
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center">
                            <flux:text class="text-zinc-500">No se encontraron notificaciones.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
