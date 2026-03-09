<?php

use App\Models\Contract;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Detalle del Contrato')] class extends Component {
    public Contract $contract;

    public function mount(Contract $contract): void
    {
        $this->contract = $contract->load(['reservation.client', 'reservation.vehicle']);
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" icon="arrow-left" href="{{ route('contracts.index') }}" wire:navigate />
                <flux:heading size="xl">Contrato {{ $contract->contractNumber() }}</flux:heading>
            </div>
            <div class="flex gap-2">
                @if($contract->pdf_path)
                    <flux:button variant="primary" icon="document-arrow-down" :href="route('contracts.download', $contract)">
                        Descargar PDF
                    </flux:button>
                @endif
                <flux:button variant="ghost" icon="printer" onclick="window.print()">
                    Imprimir
                </flux:button>
            </div>
        </div>

        {{-- Preview del contrato --}}
        <flux:card class="print:border-0 print:shadow-none">
            <div class="mx-auto max-w-3xl space-y-8 p-8 print:p-0">
                {{-- Cabecera --}}
                <div class="text-center">
                    <flux:heading size="xl" class="mb-2">CONTRATO DE ALQUILER DE VEHÍCULO</flux:heading>
                    <flux:text class="text-lg">N°: {{ $contract->contractNumber() }}</flux:text>
                </div>

                {{-- Datos del arrendador --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3 text-zinc-500">ARRENDADOR</flux:heading>
                    <dl class="space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Empresa:</dt>
                            <dd class="font-medium">HPCars S.L.</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">CIF:</dt>
                            <dd>B12345678</dd>
                        </div>
                    </dl>
                </div>

                {{-- Datos del arrendatario --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3 text-zinc-500">ARRENDATARIO</flux:heading>
                    <dl class="space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Nombre:</dt>
                            <dd class="font-medium">{{ $contract->reservation->client->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">DNI:</dt>
                            <dd>{{ $contract->reservation->client->dni ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Dirección:</dt>
                            <dd>{{ $contract->reservation->client->address ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Teléfono:</dt>
                            <dd>{{ $contract->reservation->client->phone ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Carnet de conducir:</dt>
                            <dd>{{ $contract->reservation->client->license_number ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Datos del vehículo --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3 text-zinc-500">VEHÍCULO</flux:heading>
                    <dl class="space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Marca/Modelo:</dt>
                            <dd class="font-medium">{{ $contract->reservation->vehicle->fullName() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Matrícula:</dt>
                            <dd>{{ $contract->reservation->vehicle->plate }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Año:</dt>
                            <dd>{{ $contract->reservation->vehicle->year }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Color:</dt>
                            <dd>{{ $contract->reservation->vehicle->color ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Km inicio:</dt>
                            <dd>{{ $contract->reservation->start_mileage ? number_format($contract->reservation->start_mileage, 0, ',', '.') : '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Período de alquiler --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3 text-zinc-500">PERÍODO DE ALQUILER</flux:heading>
                    <dl class="space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Fecha inicio:</dt>
                            <dd class="font-medium">{{ $contract->reservation->start_date->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Fecha fin:</dt>
                            <dd class="font-medium">{{ $contract->reservation->end_date->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Duración:</dt>
                            <dd>{{ $contract->reservation->start_date->diffInDays($contract->reservation->end_date) }} días</dd>
                        </div>
                    </dl>
                </div>

                {{-- Condiciones --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3 text-zinc-500">CONDICIONES GENERALES</flux:heading>
                    <div class="whitespace-pre-line text-sm text-zinc-700 dark:text-zinc-300">{{ $contract->conditions }}</div>
                </div>

                {{-- Firmas --}}
                <div class="grid grid-cols-2 gap-8 pt-8">
                    <div class="text-center">
                        <div class="mb-16 border-b border-zinc-300"></div>
                        <flux:text class="text-sm">El Arrendador</flux:text>
                        <flux:text class="text-xs text-zinc-500">HPCars S.L.</flux:text>
                    </div>
                    <div class="text-center">
                        <div class="mb-16 border-b border-zinc-300"></div>
                        <flux:text class="text-sm">El Arrendatario</flux:text>
                        <flux:text class="text-xs text-zinc-500">{{ $contract->reservation->client->name }}</flux:text>
                    </div>
                </div>

                {{-- Fecha de generación --}}
                <div class="text-center text-sm text-zinc-500">
                    Documento generado el {{ $contract->generated_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </flux:card>
</div>
