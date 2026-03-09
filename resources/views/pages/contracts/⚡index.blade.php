<?php

use App\Http\Controllers\ContractDownloadController;
use App\Models\Contract;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('Contratos')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    // Checklist de entrega
    public ?int $selectedReservationId = null;
    public ?int $regeneratingContractId = null;
    public string $golpes = 'sin_golpes';
    public string $limpieza = 'limpio';
    public array $equipamiento = [];
    public string $equipamientoOtros = '';
    public string $combustible = 'full';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function contracts()
    {
        return Contract::query()
            ->with(['reservation.client', 'reservation.vehicle'])
            ->when($this->search, fn (Builder $query) => $query->whereHas('reservation', function ($q) {
                $q->whereHas('client', fn ($clientQuery) => $clientQuery
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('dni', 'like', "%{$this->search}%"))
                    ->orWhereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery->where('plate', 'like', "%{$this->search}%"));
            }))
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function availableReservations()
    {
        return Reservation::query()
            ->with(['client', 'vehicle'])
            ->doesntHave('contract')
            ->orderByDesc('start_date')
            ->get();
    }

    public function generateContract(): void
    {
        $this->validate([
            'selectedReservationId' => ['required', 'exists:reservations,id'],
        ], [
            'selectedReservationId.required' => 'Debe seleccionar una reserva.',
        ]);

        $reservation = Reservation::with(['client', 'vehicle'])->findOrFail($this->selectedReservationId);

        if ($reservation->contract && $reservation->contract->pdf_path) {
            return;
        }

        $checklist = [
            'golpes' => $this->golpes,
            'limpieza' => $this->limpieza,
            'equipamiento' => $this->equipamiento,
            'equipamiento_otros' => $this->equipamientoOtros,
            'combustible' => $this->combustible,
        ];

        $pdfPath = ContractDownloadController::generatePdf($reservation, $checklist);

        Contract::create([
            'reservation_id' => $reservation->id,
            'pdf_path' => $pdfPath,
            'generated_at' => now(),
            'conditions' => $this->defaultConditions(),
        ]);

        $this->reset(['selectedReservationId', 'golpes', 'limpieza', 'equipamiento', 'equipamientoOtros', 'combustible']);
        $this->modal('generar-contrato')->close();
        unset($this->contracts, $this->availableReservations);

        session()->flash('success', 'Contrato generado correctamente.');
    }

    public function openRegenerateModal(int $contractId): void
    {
        $this->regeneratingContractId = $contractId;
        $this->reset(['golpes', 'limpieza', 'equipamiento', 'equipamientoOtros', 'combustible']);
        $this->modal('regenerar-contrato')->show();
    }

    public function regenerateContract(): void
    {
        $contract = Contract::with('reservation.client', 'reservation.vehicle')->findOrFail($this->regeneratingContractId);

        if ($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path)) {
            Storage::disk('public')->delete($contract->pdf_path);
        }

        $checklist = [
            'golpes' => $this->golpes,
            'limpieza' => $this->limpieza,
            'equipamiento' => $this->equipamiento,
            'equipamiento_otros' => $this->equipamientoOtros,
            'combustible' => $this->combustible,
        ];

        $pdfPath = ContractDownloadController::generatePdf($contract->reservation, $checklist);

        $contract->update([
            'pdf_path' => $pdfPath,
            'generated_at' => now(),
        ]);

        $this->reset(['regeneratingContractId', 'golpes', 'limpieza', 'equipamiento', 'equipamientoOtros', 'combustible']);
        $this->modal('regenerar-contrato')->close();
        unset($this->contracts);

        session()->flash('success', 'Contrato regenerado correctamente.');
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
            <flux:heading size="xl">Gestión de Contratos</flux:heading>
            @if($this->availableReservations->isNotEmpty())
                <flux:modal.trigger name="generar-contrato">
                    <flux:button variant="primary" icon="document-text">
                        Generar Contrato
                    </flux:button>
                </flux:modal.trigger>
            @endif
        </div>

        {{-- Búsqueda --}}
        <flux:card class="p-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por cliente, DNI o matrícula..." icon="magnifying-glass" />
        </flux:card>

        {{-- Tabla --}}
        <flux:card>
            <flux:table :paginate="$this->contracts">
                <flux:table.columns>
                    <flux:table.column>N° Contrato</flux:table.column>
                    <flux:table.column>Cliente</flux:table.column>
                    <flux:table.column>Vehículo</flux:table.column>
                    <flux:table.column>Fechas</flux:table.column>
                    <flux:table.column>Generado</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->contracts as $contract)
                        <flux:table.row :key="$contract->id">
                            <flux:table.cell>
                                <flux:badge>{{ $contract->contractNumber() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="font-medium">{{ $contract->reservation->client->name }}</flux:table.cell>
                            <flux:table.cell>
                                <div>
                                    <flux:text>{{ $contract->reservation->vehicle->fullName() }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500">{{ $contract->reservation->vehicle->plate }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $contract->reservation->start_date->format('d/m/Y') }} - {{ $contract->reservation->end_date->format('d/m/Y') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $contract->generated_at?->format('d/m/Y H:i') ?? '-' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-1">
                                    @if($contract->pdf_path)
                                        <flux:button variant="ghost" size="sm" icon="document-arrow-down" :href="route('contracts.download', $contract)">
                                            Descargar
                                        </flux:button>
                                    @endif
                                    <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="openRegenerateModal({{ $contract->id }})">
                                        Regenerar
                                    </flux:button>
                                    <flux:button variant="ghost" size="sm" icon="eye" :href="route('contracts.show', $contract)" wire:navigate>
                                        Ver
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center">
                                <flux:text class="text-zinc-500">No se encontraron contratos.</flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

        {{-- Modal Generar Contrato --}}
        @if($this->availableReservations->isNotEmpty())
            <flux:modal name="generar-contrato" class="md:w-[32rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Generar Contrato</flux:heading>
                        <flux:text class="mt-1">Seleccione una reserva y complete el checklist de entrega.</flux:text>
                    </div>

                    {{-- Selección de reserva --}}
                    <flux:select wire:model.live="selectedReservationId" label="Reserva" placeholder="Seleccione una reserva...">
                        @foreach($this->availableReservations as $reservation)
                            <flux:select.option :value="$reservation->id">
                                {{ $reservation->client->name }} - {{ $reservation->vehicle->plate }} ({{ $reservation->start_date->format('d/m/Y') }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    @if($selectedReservationId)
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
                    @endif

                    <div class="flex justify-end gap-2">
                        <flux:button variant="ghost" x-on:click="$flux.modal('generar-contrato').close()">
                            Cancelar
                        </flux:button>
                        <flux:button variant="primary" icon="document-text" wire:click="generateContract">
                            Generar Contrato
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        {{-- Modal Regenerar Contrato --}}
        <flux:modal name="regenerar-contrato" class="md:w-[32rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Regenerar Contrato</flux:heading>
                    <flux:text class="mt-1">Ajuste los datos del checklist y regenere el contrato.</flux:text>
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
                    <flux:button variant="ghost" x-on:click="$flux.modal('regenerar-contrato').close()">
                        Cancelar
                    </flux:button>
                    <flux:button variant="primary" icon="arrow-path" wire:click="regenerateContract">
                        Regenerar Contrato
                    </flux:button>
                </div>
            </div>
        </flux:modal>
</div>
