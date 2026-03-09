<?php

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Editar Vehiculo')] class extends Component {
    public Vehicle $vehicle;

    public string $plate = '';
    public string $brand = '';
    public string $model = '';
    public ?int $year = null;
    public string $color = '';
    public string $status = 'free';
    public ?int $current_mileage = 0;
    public ?string $itv_date = null;
    public ?string $next_maintenance_date = null;

    public function mount(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
        $this->plate = $vehicle->plate;
        $this->brand = $vehicle->brand;
        $this->model = $vehicle->model;
        $this->year = $vehicle->year;
        $this->color = $vehicle->color ?? '';
        $this->status = $vehicle->status->value;
        $this->current_mileage = $vehicle->current_mileage;
        $this->itv_date = $vehicle->itv_date?->format('Y-m-d');
        $this->next_maintenance_date = $vehicle->next_maintenance_date?->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'plate' => ['required', 'string', 'max:10', 'unique:vehicles,plate,' . $this->vehicle->id],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'string'],
            'current_mileage' => ['nullable', 'integer', 'min:0'],
            'itv_date' => ['nullable', 'date'],
            'next_maintenance_date' => ['nullable', 'date'],
        ]);

        $this->vehicle->update($validated);

        session()->flash('success', 'Vehículo actualizado correctamente.');

        $this->redirect(route('vehicles.show', $this->vehicle), navigate: true);
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('vehicles.show', $vehicle) }}" wire:navigate />
            <flux:heading size="xl">Editar Vehículo: {{ $vehicle->plate }}</flux:heading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Datos del Vehículo</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="plate" label="Matrícula" required />
                    <flux:input wire:model="brand" label="Marca" required />
                    <flux:input wire:model="model" label="Modelo" required />
                    <flux:input wire:model="year" type="number" label="Año" required />
                    <flux:input wire:model="color" label="Color" />
                    <flux:select wire:model="status" label="Estado">
                        @foreach(VehicleStatus::cases() as $statusOption)
                            <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">Mantenimiento</flux:heading>
                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="current_mileage" type="number" label="Kilometraje actual" />
                    <flux:input wire:model="itv_date" type="date" label="Fecha ITV" />
                    <flux:input wire:model="next_maintenance_date" type="date" label="Próximo mantenimiento" />
                </div>
            </flux:card>

            <div class="flex justify-end gap-4">
                <flux:button variant="ghost" href="{{ route('vehicles.show', $vehicle) }}" wire:navigate>Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar Cambios</flux:button>
            </div>
        </form>
</div>
