<?php

use App\Models\Client;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::app')] #[Title('Nuevo Cliente')] class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $company_name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $dni = '';
    public string $license_number = '';
    public string $notes = '';
    public $dni_image = null;
    public $license_image = null;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20'],
            'license_number' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'dni_image' => ['nullable', 'image', 'max:5120'],
            'license_image' => ['nullable', 'image', 'max:5120'],
        ]);

        unset($validated['dni_image'], $validated['license_image']);

        if ($this->dni_image) {
            $validated['dni_image_path'] = $this->dni_image->store('clients/dni', 'public');
        }

        if ($this->license_image) {
            $validated['license_image_path'] = $this->license_image->store('clients/licenses', 'public');
        }

        Client::create($validated);

        session()->flash('success', 'Cliente creado correctamente.');

        $this->redirect(route('clients.index'), navigate: true);
    }

    public function removeDniImage(): void
    {
        $this->dni_image = null;
    }

    public function removeLicenseImage(): void
    {
        $this->license_image = null;
    }
}; ?>

<div class="space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('clients.index') }}" wire:navigate />
            <flux:heading size="xl">Nuevo Cliente</flux:heading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Datos de Contacto</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="name" label="Nombre completo" placeholder="Juan García López" required />
                    <flux:input wire:model="company_name" label="Nombre de empresa" placeholder="Empresa S.L." />
                    <flux:input wire:model="email" type="email" label="Email" placeholder="juan@email.com" />
                    <flux:input wire:model="phone" label="Teléfono" placeholder="612345678" />
                    <flux:input wire:model="address" label="Dirección" placeholder="Calle Mayor 1, Madrid" class="sm:col-span-2" />
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">Documentación</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="dni" label="DNI/NIE" placeholder="12345678A" />
                    <flux:input wire:model="license_number" label="Nº Carnet de conducir" placeholder="B-12345678" />

                    {{-- DNI Image --}}
                    <div>
                        <flux:label>Imagen DNI</flux:label>
                        <div class="mt-1">
                            @if ($dni_image && $dni_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile && $dni_image->isPreviewable())
                                <div class="relative mb-2">
                                    <img src="{{ $dni_image->temporaryUrl() }}" alt="Vista previa DNI" class="h-32 w-auto rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                                    <button type="button" wire:click="removeDniImage" class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white shadow hover:bg-red-600">
                                        <x-flux::icon.x-mark class="size-3" />
                                    </button>
                                </div>
                            @endif
                            <input type="file" wire:model="dni_image" accept="image/*" class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:text-zinc-400 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700">
                            <div wire:loading wire:target="dni_image" class="mt-1 text-sm text-zinc-500">Subiendo...</div>
                            @error('dni_image') <span class="mt-1 text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- License Image --}}
                    <div>
                        <flux:label>Imagen Carnet de conducir</flux:label>
                        <div class="mt-1">
                            @if ($license_image && $license_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile && $license_image->isPreviewable())
                                <div class="relative mb-2">
                                    <img src="{{ $license_image->temporaryUrl() }}" alt="Vista previa Carnet" class="h-32 w-auto rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                                    <button type="button" wire:click="removeLicenseImage" class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white shadow hover:bg-red-600">
                                        <x-flux::icon.x-mark class="size-3" />
                                    </button>
                                </div>
                            @endif
                            <input type="file" wire:model="license_image" accept="image/*" class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:text-zinc-400 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700">
                            <div wire:loading wire:target="license_image" class="mt-1 text-sm text-zinc-500">Subiendo...</div>
                            @error('license_image') <span class="mt-1 text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">Notas</flux:heading>
                <flux:textarea wire:model="notes" placeholder="Observaciones sobre el cliente..." rows="3" />
            </flux:card>

            <div class="flex justify-end gap-4">
                <flux:button variant="ghost" href="{{ route('clients.index') }}" wire:navigate>Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar Cliente</flux:button>
            </div>
        </form>
</div>
