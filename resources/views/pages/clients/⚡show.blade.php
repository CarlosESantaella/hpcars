<?php

use App\Models\Client;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Detalle del Cliente')] class extends Component {
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    #[Computed]
    public function reservationHistory()
    {
        return $this->client->reservations()
            ->with(['vehicle', 'contract'])
            ->orderByDesc('start_date')
            ->get();
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" icon="arrow-left" href="{{ route('clients.index') }}" wire:navigate />
                <flux:heading size="xl">{{ $client->name }}</flux:heading>
            </div>
            <div class="flex gap-2">
                <flux:button variant="primary" icon="pencil" href="{{ route('clients.edit', $client) }}" wire:navigate>
                    Editar
                </flux:button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Datos de contacto --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Datos de Contacto</flux:heading>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Nombre</dt>
                        <dd class="font-medium">{{ $client->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Empresa</dt>
                        <dd>{{ $client->company_name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Email</dt>
                        <dd>{{ $client->email ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Teléfono</dt>
                        <dd>{{ $client->phone ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Dirección</dt>
                        <dd>{{ $client->address ?? '-' }}</dd>
                    </div>
                </dl>
            </flux:card>

            {{-- Documentación --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Documentación</flux:heading>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">DNI/NIE</dt>
                        <dd>
                            @if($client->dni)
                                <flux:badge>{{ $client->dni }}</flux:badge>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Carnet de conducir</dt>
                        <dd>{{ $client->license_number ?? '-' }}</dd>
                    </div>
                </dl>

                {{-- Document Images --}}
                @if($client->dni_image_path || $client->license_image_path)
                    <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        <flux:text class="mb-3 font-medium text-zinc-500">Imágenes de documentos</flux:text>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($client->dni_image_path)
                                <div>
                                    <flux:text class="mb-1 text-sm text-zinc-500">DNI/NIE</flux:text>
                                    <a href="{{ $client->dniImageUrl() }}" target="_blank">
                                        <img src="{{ $client->dniImageUrl() }}" alt="DNI de {{ $client->name }}" class="h-40 w-auto rounded-lg border border-zinc-200 object-cover transition hover:opacity-80 dark:border-zinc-700">
                                    </a>
                                </div>
                            @endif
                            @if($client->license_image_path)
                                <div>
                                    <flux:text class="mb-1 text-sm text-zinc-500">Carnet de conducir</flux:text>
                                    <a href="{{ $client->licenseImageUrl() }}" target="_blank">
                                        <img src="{{ $client->licenseImageUrl() }}" alt="Carnet de {{ $client->name }}" class="h-40 w-auto rounded-lg border border-zinc-200 object-cover transition hover:opacity-80 dark:border-zinc-700">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Notas --}}
        @if($client->notes)
            <flux:card>
                <flux:heading size="lg" class="mb-4">Notas</flux:heading>
                <flux:text>{{ $client->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Historial de alquileres --}}
        <flux:card>
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">Historial de Alquileres</flux:heading>
                <flux:badge>{{ $this->reservationHistory->count() }} alquileres</flux:badge>
            </div>

            @if($this->reservationHistory->isEmpty())
                <flux:text class="text-zinc-500">Este cliente no tiene historial de alquileres.</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Fechas</flux:table.column>
                        <flux:table.column>Vehículo</flux:table.column>
                        <flux:table.column>Estado</flux:table.column>
                        <flux:table.column>Contrato</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->reservationHistory as $reservation)
                            <flux:table.row :key="$reservation->id">
                                <flux:table.cell>
                                    {{ $reservation->start_date->format('d/m/Y') }} - {{ $reservation->end_date->format('d/m/Y') }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div>
                                        <flux:text class="font-medium">{{ $reservation->vehicle->fullName() }}</flux:text>
                                        <flux:text class="text-xs text-zinc-500">{{ $reservation->vehicle->plate }}</flux:text>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$reservation->status->color()" size="sm">
                                        {{ $reservation->status->label() }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($reservation->contract && $reservation->contract->pdf_path)
                                        <flux:button size="xs" variant="ghost" icon="document-arrow-down" :href="route('contracts.download', $reservation->contract)">
                                            Descargar
                                        </flux:button>
                                    @elseif($reservation->contract)
                                        <flux:button size="xs" variant="ghost" icon="eye" :href="route('contracts.show', $reservation->contract)" wire:navigate>
                                            Ver
                                        </flux:button>
                                    @else
                                        <flux:text class="text-zinc-400">-</flux:text>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
</div>
