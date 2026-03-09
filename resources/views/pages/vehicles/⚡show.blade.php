<?php

use App\Models\Vehicle;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Detalle del Vehiculo')] class extends Component {
    public Vehicle $vehicle;

    public function mount(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle->load(['reservations.client']);
    }

    #[Computed]
    public function reservationHistory()
    {
        return $this->vehicle->reservations()
            ->with('client')
            ->orderByDesc('start_date')
            ->limit(10)
            ->get();
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" icon="arrow-left" href="{{ route('vehicles.index') }}" wire:navigate />
                <div>
                    <flux:heading size="xl">{{ $vehicle->fullName() }}</flux:heading>
                    <flux:text class="text-zinc-500">{{ $vehicle->plate }}</flux:text>
                </div>
            </div>
            <div class="flex gap-2">
                <flux:button variant="primary" icon="pencil" href="{{ route('vehicles.edit', $vehicle) }}" wire:navigate>
                    Editar
                </flux:button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Datos básicos --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Datos del Vehículo</flux:heading>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Matrícula</dt>
                        <dd><flux:badge>{{ $vehicle->plate }}</flux:badge></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Marca</dt>
                        <dd>{{ $vehicle->brand }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Modelo</dt>
                        <dd>{{ $vehicle->model }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Año</dt>
                        <dd>{{ $vehicle->year }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Color</dt>
                        <dd>{{ $vehicle->color ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Estado</dt>
                        <dd><flux:badge :color="$vehicle->status->color()">{{ $vehicle->status->label() }}</flux:badge></dd>
                    </div>
                </dl>
            </flux:card>

            {{-- Mantenimiento --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Estado y Mantenimiento</flux:heading>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Kilometraje actual</dt>
                        <dd class="font-medium">{{ number_format($vehicle->current_mileage, 0, ',', '.') }} km</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Fecha ITV</dt>
                        <dd>
                            @if($vehicle->itv_date)
                                <span class="{{ $vehicle->itv_date->isPast() ? 'text-red-600' : ($vehicle->itv_date->diffInDays(now()) <= 30 ? 'text-amber-600' : '') }}">
                                    {{ $vehicle->itv_date->format('d/m/Y') }}
                                </span>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Próximo mantenimiento</dt>
                        <dd>
                            @if($vehicle->next_maintenance_date)
                                <span class="{{ $vehicle->next_maintenance_date->isPast() ? 'text-red-600' : ($vehicle->next_maintenance_date->diffInDays(now()) <= 7 ? 'text-amber-600' : '') }}">
                                    {{ $vehicle->next_maintenance_date->format('d/m/Y') }}
                                </span>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                </dl>
            </flux:card>
        </div>

        {{-- Historial de alquileres --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">Historial de Alquileres</flux:heading>
            @if($this->reservationHistory->isEmpty())
                <flux:text class="text-zinc-500">Este vehículo no tiene historial de alquileres.</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Fecha inicio</flux:table.column>
                        <flux:table.column>Fecha fin</flux:table.column>
                        <flux:table.column>Cliente</flux:table.column>
                        <flux:table.column>Km inicio</flux:table.column>
                        <flux:table.column>Km fin</flux:table.column>
                        <flux:table.column>Estado</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->reservationHistory as $reservation)
                            <flux:table.row :key="$reservation->id">
                                <flux:table.cell>{{ $reservation->start_date->format('d/m/Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $reservation->end_date->format('d/m/Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $reservation->client->name }}</flux:table.cell>
                                <flux:table.cell>{{ $reservation->start_mileage ? number_format($reservation->start_mileage, 0, ',', '.') : '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $reservation->end_mileage ? number_format($reservation->end_mileage, 0, ',', '.') : '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$reservation->status->color()" size="sm">
                                        {{ $reservation->status->label() }}
                                    </flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
</div>
