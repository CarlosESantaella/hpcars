<?php

use App\Enums\ReservationStatus;
use App\Enums\VehicleStatus;
use App\Http\Controllers\ContractDownloadController;
use App\Models\Contract;
use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Detalle de Reserva')] class extends Component {
    public Reservation $reservation;

    // Checklist entrega
    public string $golpes = 'sin_golpes';
    public string $limpieza = 'limpio';
    public array $equipamiento = [];
    public string $equipamientoOtros = '';
    public string $combustible = 'full';

    public function mount(Reservation $reservation): void
    {
        $this->reservation = $reservation->load(['client', 'vehicle', 'contract']);
    }

    public bool $isRegenerating = false;

    public function generateContract(): void
    {
        if ($this->reservation->contract && $this->reservation->contract->pdf_path) {
            return;
        }

        $pdfPath = $this->buildContractPdf();

        Contract::create([
            'reservation_id' => $this->reservation->id,
            'pdf_path' => $pdfPath,
            'generated_at' => now(),
            'conditions' => $this->defaultConditions(),
        ]);

        $this->reservation->load('contract');
        $this->modal('checklist-entrega')->close();

        session()->flash('success', 'Contrato generado correctamente.');
    }

    public function openRegenerateModal(): void
    {
        $this->isRegenerating = true;
        $this->modal('checklist-entrega')->close();
        $this->modal('checklist-entrega')->show();
    }

    public function regenerateContract(): void
    {
        $contract = $this->reservation->contract;

        if (! $contract) {
            return;
        }

        // Eliminar PDF anterior
        if ($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path)) {
            Storage::disk('public')->delete($contract->pdf_path);
        }

        $pdfPath = $this->buildContractPdf();

        $contract->update([
            'pdf_path' => $pdfPath,
            'generated_at' => now(),
        ]);

        $this->reservation->load('contract');
        $this->isRegenerating = false;
        $this->modal('checklist-entrega')->close();

        session()->flash('success', 'Contrato regenerado correctamente.');
    }

    private function buildContractPdf(): string
    {
        $checklist = [
            'golpes' => $this->golpes,
            'limpieza' => $this->limpieza,
            'equipamiento' => $this->equipamiento,
            'equipamiento_otros' => $this->equipamientoOtros,
            'combustible' => $this->combustible,
        ];

        return ContractDownloadController::generatePdf($this->reservation, $checklist);
    }

    public function activateReservation(): void
    {
        $this->reservation->update(['status' => ReservationStatus::Active]);
        $this->reservation->vehicle->update(['status' => VehicleStatus::Rented]);
        $this->reservation->refresh();
    }

    public function completeReservation(): void
    {
        $this->reservation->update(['status' => ReservationStatus::Completed]);
        $this->reservation->vehicle->update(['status' => VehicleStatus::Free]);
        $this->reservation->refresh();
    }

    public function cancelReservation(): void
    {
        $this->reservation->update(['status' => ReservationStatus::Cancelled]);
        if ($this->reservation->vehicle->status === VehicleStatus::Rented) {
            $this->reservation->vehicle->update(['status' => VehicleStatus::Free]);
        }
        $this->reservation->refresh();
    }

    private function defaultConditions(): string
    {
        return <<<'CONDITIONS'
1. El arrendatario se compromete a utilizar el vehículo de forma responsable y conforme a las normas de circulación vigentes.
2. El vehículo deberá ser devuelto en las mismas condiciones en que fue entregado, salvo el desgaste normal por uso.
3. El arrendatario será responsable de cualquier multa o sanción derivada del uso del vehículo durante el período de alquiler.
4. En caso de accidente, el arrendatario deberá notificar inmediatamente a la empresa arrendadora.
5. Queda prohibido fumar en el interior del vehículo.
6. El combustible no está incluido en el precio del alquiler. El vehículo se entrega con el depósito lleno y deberá devolverse en las mismas condiciones.
CONDITIONS;
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" icon="arrow-left" href="{{ route('reservations.index') }}" wire:navigate />
                <div>
                    <flux:heading size="xl">Reserva #{{ $reservation->id }}</flux:heading>
                    <flux:badge :color="$reservation->status->color()">{{ $reservation->status->label() }}</flux:badge>
                </div>
            </div>
            <div class="flex gap-2">
                <flux:button variant="ghost" icon="pencil" href="{{ route('reservations.edit', $reservation) }}" wire:navigate>
                    Editar
                </flux:button>
                @if(!$reservation->contract || !$reservation->contract->pdf_path)
                    <flux:modal.trigger name="checklist-entrega">
                        <flux:button variant="primary" icon="document-text">
                            Generar Contrato
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        </div>

        {{-- Acciones rápidas --}}
        @if($reservation->status !== \App\Enums\ReservationStatus::Completed && $reservation->status !== \App\Enums\ReservationStatus::Cancelled)
            <flux:card class="p-4">
                <div class="flex flex-wrap gap-2">
                    @if($reservation->status === \App\Enums\ReservationStatus::Pending)
                        <flux:button variant="primary" wire:click="activateReservation" wire:confirm="¿Activar esta reserva? El vehículo pasará a estado 'Alquilado'.">
                            Activar Reserva
                        </flux:button>
                    @endif
                    @if($reservation->status === \App\Enums\ReservationStatus::Active)
                        <flux:button variant="primary" wire:click="completeReservation" wire:confirm="¿Completar esta reserva? El vehículo volverá a estar disponible.">
                            Completar Reserva
                        </flux:button>
                    @endif
                    <flux:button variant="danger" wire:click="cancelReservation" wire:confirm="¿Cancelar esta reserva?">
                        Cancelar Reserva
                    </flux:button>
                </div>
            </flux:card>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Cliente --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Cliente</flux:heading>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Nombre</dt>
                        <dd class="font-medium">{{ $reservation->client->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">DNI</dt>
                        <dd>{{ $reservation->client->dni ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Teléfono</dt>
                        <dd>{{ $reservation->client->phone ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Email</dt>
                        <dd>{{ $reservation->client->email ?? '-' }}</dd>
                    </div>
                </dl>
                <div class="mt-4">
                    <flux:button variant="ghost" size="sm" :href="route('clients.show', $reservation->client)" wire:navigate>
                        Ver ficha completa
                    </flux:button>
                </div>
            </flux:card>

            {{-- Vehículo --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">Vehículo</flux:heading>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Vehículo</dt>
                        <dd class="font-medium">{{ $reservation->vehicle->fullName() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Matrícula</dt>
                        <dd><flux:badge>{{ $reservation->vehicle->plate }}</flux:badge></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Estado actual</dt>
                        <dd><flux:badge :color="$reservation->vehicle->status->color()">{{ $reservation->vehicle->status->label() }}</flux:badge></dd>
                    </div>
                </dl>
                <div class="mt-4">
                    <flux:button variant="ghost" size="sm" :href="route('vehicles.show', $reservation->vehicle)" wire:navigate>
                        Ver ficha completa
                    </flux:button>
                </div>
            </flux:card>
        </div>

        {{-- Detalles de la reserva --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">Detalles de la Reserva</flux:heading>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <flux:text class="text-sm text-zinc-500">Fecha inicio</flux:text>
                    <flux:text class="text-lg font-medium">{{ $reservation->start_date->format('d/m/Y') }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Fecha fin</flux:text>
                    <flux:text class="text-lg font-medium">{{ $reservation->end_date->format('d/m/Y') }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Km inicio</flux:text>
                    <flux:text class="text-lg font-medium">{{ $reservation->start_mileage ? number_format($reservation->start_mileage, 0, ',', '.') : '-' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Km fin</flux:text>
                    <flux:text class="text-lg font-medium">{{ $reservation->end_mileage ? number_format($reservation->end_mileage, 0, ',', '.') : '-' }}</flux:text>
                </div>
            </div>
        </flux:card>

        {{-- Contrato --}}
        @if($reservation->contract && $reservation->contract->pdf_path)
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Contrato</flux:heading>
                        <flux:text class="text-zinc-500">Generado el {{ $reservation->contract->generated_at?->format('d/m/Y H:i') }}</flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:button variant="ghost" icon="arrow-path" wire:click="openRegenerateModal">
                            Regenerar
                        </flux:button>
                        <flux:button variant="primary" icon="document-arrow-down" :href="route('contracts.download', $reservation->contract)">
                            Descargar Contrato
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @endif

        {{-- Modal Checklist Entrega --}}
        <flux:modal name="checklist-entrega" class="md:w-[32rem]" x-on:close="$wire.set('isRegenerating', false)">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $isRegenerating ? 'Regenerar Contrato' : 'Checklist de Entrega' }}</flux:heading>
                    <flux:text class="mt-1">{{ $isRegenerating ? 'Ajuste los datos y regenere el contrato.' : 'Complete los datos del vehículo antes de generar el contrato.' }}</flux:text>
                </div>

                {{-- Golpes --}}
                <flux:radio.group wire:model="golpes" label="Golpes">
                    <flux:radio value="sin_golpes" label="Sin golpes" />
                    <flux:radio value="tiene_golpes" label="Tiene golpes" />
                </flux:radio.group>

                {{-- Limpieza --}}
                <flux:radio.group wire:model="limpieza" label="Limpieza">
                    <flux:radio value="limpio" label="Limpio" />
                    <flux:radio value="sucio" label="Sucio" />
                    <flux:radio value="extremadamente_sucio" label="Extremadamente sucio" />
                </flux:radio.group>

                {{-- Equipamiento --}}
                <flux:checkbox.group wire:model="equipamiento" label="Equipamiento">
                    <flux:checkbox value="triangulos" label="Triángulos" />
                    <flux:checkbox value="chaleco" label="Chaleco" />
                    <flux:checkbox value="llave_ruedas" label="Llave de ruedas" />
                    <flux:checkbox value="compresor_gato" label="Compresor / Gato" />
                    <flux:checkbox value="baliza_v16" label="Baliza V16" />
                </flux:checkbox.group>

                <flux:input wire:model="equipamientoOtros" label="Otros equipamientos" placeholder="Especificar..." />

                {{-- Combustible --}}
                <flux:radio.group wire:model="combustible" label="Nivel de combustible" variant="segmented">
                    <flux:radio value="E" label="E" />
                    <flux:radio value="1/4" label="1/4" />
                    <flux:radio value="1/2" label="1/2" />
                    <flux:radio value="3/4" label="3/4" />
                    <flux:radio value="full" label="Full" />
                </flux:radio.group>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" x-on:click="$flux.modal('checklist-entrega').close()">
                        Cancelar
                    </flux:button>
                    @if($isRegenerating)
                        <flux:button variant="primary" icon="arrow-path" wire:click="regenerateContract">
                            Regenerar Contrato
                        </flux:button>
                    @else
                        <flux:button variant="primary" icon="document-text" wire:click="generateContract">
                            Generar Contrato
                        </flux:button>
                    @endif
                </div>
            </div>
        </flux:modal>
</div>
