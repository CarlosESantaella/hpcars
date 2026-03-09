<?php

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Vehicle;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Editar Reserva')] class extends Component {
    public Reservation $reservation;

    public ?int $client_id = null;
    public ?int $vehicle_id = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $status = 'pending';
    public ?int $start_mileage = null;
    public ?int $end_mileage = null;

    public function mount(Reservation $reservation): void
    {
        $this->reservation = $reservation;
        $this->client_id = $reservation->client_id;
        $this->vehicle_id = $reservation->vehicle_id;
        $this->start_date = $reservation->start_date->format('Y-m-d');
        $this->end_date = $reservation->end_date->format('Y-m-d');
        $this->status = $reservation->status->value;
        $this->start_mileage = $reservation->start_mileage;
        $this->end_mileage = $reservation->end_mileage;
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy('name')->get();
    }

    #[Computed]
    public function vehicles()
    {
        return Vehicle::orderBy('brand')->get();
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
            'end_mileage' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->reservation->update($validated);

        session()->flash('success', 'Reserva actualizada correctamente.');

        $this->redirect(route('reservations.show', $this->reservation), navigate: true);
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('reservations.show', $reservation) }}" wire:navigate />
            <flux:heading size="xl">Editar Reserva #{{ $reservation->id }}</flux:heading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Cliente --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Cliente</flux:heading>
                    <flux:select wire:model="client_id" label="Cliente" required>
                        @foreach($this->clients as $client)
                            <flux:select.option value="{{ $client->id }}">{{ $client->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:card>

                {{-- Vehículo --}}
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Vehículo</flux:heading>
                    <flux:select wire:model="vehicle_id" label="Vehículo" required>
                        @foreach($this->vehicles as $vehicle)
                            <flux:select.option value="{{ $vehicle->id }}">{{ $vehicle->fullName() }} ({{ $vehicle->plate }})</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:card>
            </div>

            {{-- Fechas y estado --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Detalles de la Reserva</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <flux:input wire:model="start_date" type="date" label="Fecha inicio" required />
                    <flux:input wire:model="end_date" type="date" label="Fecha fin" required />
                    <flux:select wire:model="status" label="Estado">
                        @foreach(ReservationStatus::cases() as $statusOption)
                            <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </flux:card>

            {{-- Kilometraje --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Kilometraje</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="start_mileage" type="number" label="Km inicio" />
                    <flux:input wire:model="end_mileage" type="number" label="Km fin" />
                </div>
            </flux:card>

            <div class="flex justify-end gap-4">
                <flux:button variant="ghost" href="{{ route('reservations.show', $reservation) }}" wire:navigate>Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar Cambios</flux:button>
            </div>
        </form>
</div>
