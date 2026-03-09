<?php

use App\Enums\ReservationStatus;
use App\Enums\VehicleStatus;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Vehicle;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Nueva Reserva')] class extends Component {
    public ?int $client_id = null;
    public ?int $vehicle_id = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $status = 'pending';
    public ?int $start_mileage = null;

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(3)->format('Y-m-d');
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy('name')->get();
    }

    #[Computed]
    public function availableVehicles()
    {
        if (empty($this->start_date) || empty($this->end_date)) {
            return Vehicle::where('status', VehicleStatus::Free)->orderBy('brand')->get();
        }

        return Vehicle::availableBetween($this->start_date, $this->end_date)
            ->orderBy('brand')
            ->get();
    }

    public function updatedStartDate(): void
    {
        if ($this->start_date && $this->end_date && $this->start_date > $this->end_date) {
            $this->end_date = $this->start_date;
        }
        $this->vehicle_id = null;
    }

    public function updatedEndDate(): void
    {
        $this->vehicle_id = null;
    }

    public function updatedVehicleId(): void
    {
        if ($this->vehicle_id) {
            $vehicle = Vehicle::find($this->vehicle_id);
            $this->start_mileage = $vehicle?->current_mileage;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string'],
            'start_mileage' => ['nullable', 'integer', 'min:0'],
        ]);

        Reservation::create($validated);

        session()->flash('success', 'Reserva creada correctamente.');

        $this->redirect(route('reservations.index'), navigate: true);
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('reservations.index') }}" wire:navigate />
            <flux:heading size="xl">Nueva Reserva</flux:heading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Cliente --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Cliente</flux:heading>
                    <flux:select wire:model="client_id" label="Seleccionar cliente" required>
                        <flux:select.option value="">Selecciona un cliente...</flux:select.option>
                        @foreach($this->clients as $client)
                            <flux:select.option value="{{ $client->id }}">{{ $client->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <div class="mt-4">
                        <flux:button variant="ghost" size="sm" icon="plus" href="{{ route('clients.create') }}" wire:navigate>
                            Crear cliente nuevo
                        </flux:button>
                    </div>
                </flux:card>

                {{-- Fechas --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Fechas</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model.live="start_date" type="date" label="Fecha inicio" required />
                        <flux:input wire:model.live="end_date" type="date" label="Fecha fin" required />
                    </div>
                </flux:card>
            </div>

            {{-- Vehículo --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Vehículo Disponible</flux:heading>
                @if($this->availableVehicles->isEmpty())
                    <flux:text class="text-amber-600">No hay vehículos disponibles para las fechas seleccionadas.</flux:text>
                @else
                    <div class="space-y-3">
                        @foreach($this->availableVehicles as $vehicle)
                            <label class="flex cursor-pointer items-center gap-4 rounded-lg border p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $vehicle_id == $vehicle->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                                <input type="radio" wire:model.live="vehicle_id" value="{{ $vehicle->id }}" class="sr-only" />
                                <div class="flex h-5 w-5 items-center justify-center rounded-full border-2 {{ $vehicle_id == $vehicle->id ? 'border-blue-500' : 'border-zinc-300' }}">
                                    @if($vehicle_id == $vehicle->id)
                                        <div class="h-2.5 w-2.5 rounded-full bg-blue-500"></div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <flux:text class="font-medium">{{ $vehicle->fullName() }}</flux:text>
                                    <flux:text class="text-sm text-zinc-500">{{ $vehicle->plate }} - {{ number_format($vehicle->current_mileage, 0, ',', '.') }} km</flux:text>
                                </div>
                                <flux:badge :color="$vehicle->status->color()" size="sm">{{ $vehicle->status->label() }}</flux:badge>
                            </label>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            {{-- Detalles adicionales --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Detalles</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="start_mileage" type="number" label="Kilometraje inicio" />
                    <flux:select wire:model="status" label="Estado">
                        @foreach(ReservationStatus::cases() as $statusOption)
                            <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </flux:card>

            <div class="flex justify-end gap-4">
                <flux:button variant="ghost" href="{{ route('reservations.index') }}" wire:navigate>Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar Reserva</flux:button>
            </div>
        </form>
</div>
