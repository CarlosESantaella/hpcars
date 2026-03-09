<?php

use App\Enums\ReservationStatus;
use App\Enums\VehicleStatus;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function freeVehicles(): int
    {
        return Vehicle::where('status', VehicleStatus::Free)->count();
    }

    #[Computed]
    public function rentedVehicles(): int
    {
        return Vehicle::where('status', VehicleStatus::Rented)->count();
    }

    #[Computed]
    public function maintenanceVehicles(): int
    {
        return Vehicle::where('status', VehicleStatus::Maintenance)->count();
    }

    #[Computed]
    public function activeReservations(): int
    {
        return Reservation::whereIn('status', [ReservationStatus::Active, ReservationStatus::Pending])->count();
    }

    #[Computed]
    public function alerts(): Collection
    {
        $alerts = collect();

        // Vehículos con ITV próxima a vencer (14 días)
        Vehicle::where('itv_date', '<=', now()->addDays(14))
            ->where('itv_date', '>=', now())
            ->each(function ($vehicle) use ($alerts) {
                $alerts->push([
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'message' => "ITV vence en {$vehicle->itv_date->diffInDays(now())} días: {$vehicle->plate} ({$vehicle->fullName()})",
                ]);
            });

        // Vehículos con mantenimiento programado próximo (7 días)
        Vehicle::where('next_maintenance_date', '<=', now()->addDays(7))
            ->where('next_maintenance_date', '>=', now())
            ->each(function ($vehicle) use ($alerts) {
                $alerts->push([
                    'type' => 'info',
                    'icon' => 'wrench-screwdriver',
                    'message' => "Mantenimiento programado: {$vehicle->plate} ({$vehicle->fullName()})",
                ]);
            });

        return $alerts;
    }

    #[Computed]
    public function notifications(): Collection
    {
        return Notification::with('vehicle')
            ->unread()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function unreadNotificationsCount(): int
    {
        return Notification::unread()->count();
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->update(['is_read' => true]);
        unset($this->notifications, $this->unreadNotificationsCount);
    }

    public function markAllAsRead(): void
    {
        Notification::unread()->update(['is_read' => true]);
        unset($this->notifications, $this->unreadNotificationsCount);
    }

    #[Computed]
    public function todayReservations(): Collection
    {
        return Reservation::with(['client', 'vehicle'])
            ->where(function ($query) {
                $query->where('start_date', now()->toDateString())
                    ->orWhere('end_date', now()->toDateString());
            })
            ->whereIn('status', [ReservationStatus::Active, ReservationStatus::Pending])
            ->get()
            ->map(function ($reservation) {
                $isPickup = $reservation->start_date->isToday();
                return [
                    'id' => $reservation->id,
                    'client' => $reservation->client->name,
                    'vehicle' => $reservation->vehicle->fullName(),
                    'plate' => $reservation->vehicle->plate,
                    'type' => $isPickup ? 'Recogida' : 'Entrega',
                    'type_color' => $isPickup ? 'emerald' : 'amber',
                ];
            });
    }
}; ?>

<div class="space-y-6">
    <flux:heading size="xl">Panel</flux:heading>

    {{-- Estadísticas --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">Vehículos Libres</flux:text>
                <flux:badge color="green" size="sm">Disponible</flux:badge>
            </div>
            <flux:heading size="xl">{{ $this->freeVehicles }}</flux:heading>
        </flux:card>

        <flux:card class="space-y-2">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">Vehículos Alquilados</flux:text>
                <flux:badge color="yellow" size="sm">En uso</flux:badge>
            </div>
            <flux:heading size="xl">{{ $this->rentedVehicles }}</flux:heading>
        </flux:card>

        <flux:card class="space-y-2">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">En Mantenimiento</flux:text>
                <flux:badge color="red" size="sm">No disponible</flux:badge>
            </div>
            <flux:heading size="xl">{{ $this->maintenanceVehicles }}</flux:heading>
        </flux:card>

        <flux:card class="space-y-2">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">Reservas Activas</flux:text>
                <flux:badge color="blue" size="sm">Pendientes</flux:badge>
            </div>
            <flux:heading size="xl">{{ $this->activeReservations }}</flux:heading>
        </flux:card>
    </div>

    {{-- Alertas --}}
    @if($this->alerts->isNotEmpty())
        <flux:card>
            <flux:heading size="lg" class="mb-4">Alertas</flux:heading>
            <div class="space-y-2">
                @foreach($this->alerts as $alert)
                    <div class="flex items-center gap-3 rounded-lg p-3 {{ $alert['type'] === 'warning' ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                        <flux:icon :name="$alert['icon']" class="{{ $alert['type'] === 'warning' ? 'text-amber-600' : 'text-blue-600' }}" />
                        <flux:text class="{{ $alert['type'] === 'warning' ? 'text-amber-800 dark:text-amber-200' : 'text-blue-800 dark:text-blue-200' }}">
                            {{ $alert['message'] }}
                        </flux:text>
                    </div>
                @endforeach
            </div>
        </flux:card>
    @endif

    {{-- Notificaciones --}}
    @if($this->notifications->isNotEmpty())
        <flux:card>
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:heading size="lg">Notificaciones</flux:heading>
                    <flux:badge color="red" size="sm">{{ $this->unreadNotificationsCount }}</flux:badge>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button size="xs" variant="ghost" wire:click="markAllAsRead">
                        Marcar todas como leídas
                    </flux:button>
                    <flux:button size="xs" variant="ghost" :href="route('notifications.index')" wire:navigate>
                        Ver todas
                    </flux:button>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($this->notifications as $notification)
                    <div wire:key="notification-{{ $notification->id }}" class="flex items-center justify-between gap-3 rounded-lg p-3 {{ $notification->type->value === 'itv' ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                        <div class="flex items-center gap-3">
                            <flux:icon :name="$notification->type->icon()" class="{{ $notification->type->value === 'itv' ? 'text-amber-600' : 'text-blue-600' }}" />
                            <div>
                                <flux:text class="font-medium {{ $notification->type->value === 'itv' ? 'text-amber-800 dark:text-amber-200' : 'text-blue-800 dark:text-blue-200' }}">
                                    {{ $notification->title }}
                                </flux:text>
                                <flux:text class="text-sm {{ $notification->type->value === 'itv' ? 'text-amber-700 dark:text-amber-300' : 'text-blue-700 dark:text-blue-300' }}">
                                    {{ $notification->message }}
                                </flux:text>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <flux:badge size="sm" :color="$notification->type->color()">{{ $notification->type->label() }}</flux:badge>
                            <flux:button size="xs" variant="ghost" wire:click="markAsRead({{ $notification->id }})" title="Marcar como leída">
                                <flux:icon name="check" class="size-4" />
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>
    @endif

    {{-- Reservas de hoy --}}
    <flux:card>
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">Reservas Hoy</flux:heading>
            <flux:badge>{{ now()->format('d/m/Y') }}</flux:badge>
        </div>

        @if($this->todayReservations->isEmpty())
            <flux:text class="text-zinc-500">No hay reservas programadas para hoy.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Cliente</flux:table.column>
                    <flux:table.column>Vehículo</flux:table.column>
                    <flux:table.column>Matrícula</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->todayReservations as $reservation)
                        <flux:table.row :key="$reservation['id']">
                            <flux:table.cell>{{ $reservation['client'] }}</flux:table.cell>
                            <flux:table.cell>{{ $reservation['vehicle'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm">{{ $reservation['plate'] }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$reservation['type_color']" size="sm">
                                    {{ $reservation['type'] }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="xs" variant="ghost" :href="route('reservations.show', $reservation['id'])" wire:navigate>
                                    Ver
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
