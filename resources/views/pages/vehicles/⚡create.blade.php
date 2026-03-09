<?php

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Nuevo Vehículo')] class extends Component {
    public string $plate = '';
    public string $brand = '';
    public string $model = '';
    public ?int $year = null;
    public string $color = '';
    public string $status = 'free';
    public ?int $current_mileage = 0;
    public ?string $itv_date = null;
    public ?string $next_maintenance_date = null;

    public function save(): void
    {
        $validated = $this->validate([
            'plate' => ['required', 'string', 'max:10', 'unique:vehicles,plate'],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'string'],
            'current_mileage' => ['nullable', 'integer', 'min:0'],
            'itv_date' => ['nullable', 'date'],
            'next_maintenance_date' => ['nullable', 'date'],
        ]);

        Vehicle::create($validated);

        session()->flash('success', 'Vehículo creado correctamente.');

        $this->redirect(route('vehicles.index'), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('vehicles.index') }}" wire:navigate />
        <flux:heading size="xl">Nuevo Vehículo</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">Datos del Vehículo</flux:heading>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="plate" label="Matrícula" placeholder="1234ABC" required />
                <flux:input wire:model="brand" label="Marca" placeholder="Seat" required />
                <flux:input wire:model="model" label="Modelo" placeholder="Ibiza" required />
                <flux:input wire:model="year" type="number" label="Año" placeholder="2023" required />
                <flux:input wire:model="color" label="Color" placeholder="Blanco" />
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
                <flux:input wire:model="current_mileage" type="number" label="Kilometraje actual" placeholder="0" />
                <flux:input wire:model="itv_date" type="date" label="Fecha ITV" />
                <flux:input wire:model="next_maintenance_date" type="date" label="Próximo mantenimiento" />
            </div>
        </flux:card>

        <div class="flex justify-end gap-4">
            <flux:button variant="ghost" href="{{ route('vehicles.index') }}" wire:navigate>Cancelar</flux:button>
            <flux:button variant="primary" type="submit">Guardar Vehículo</flux:button>
        </div>
    </form>
</div>
