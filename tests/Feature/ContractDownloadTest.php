<?php

namespace Tests\Feature;

use App\Http\Controllers\ContractDownloadController;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_download_requires_authentication(): void
    {
        $contract = Contract::factory()->create(['pdf_path' => 'contracts/test.pdf']);

        $response = $this->get(route('contracts.download', $contract));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_contract_pdf(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $client = Client::factory()->create([
            'name' => 'Juan Garcia',
            'dni' => '12345678A',
            'address' => 'Calle Mayor 123, Vigo',
        ]);
        $vehicle = Vehicle::factory()->create([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'plate' => '1234ABC',
        ]);
        $reservation = Reservation::factory()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $pdfPath = 'contracts/test_contract.pdf';
        Storage::disk('public')->put($pdfPath, 'fake pdf content');

        $contract = Contract::factory()->create([
            'reservation_id' => $reservation->id,
            'pdf_path' => $pdfPath,
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('contracts.download', $contract));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_contract_download_returns_404_if_pdf_missing(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $contract = Contract::factory()->create([
            'pdf_path' => 'contracts/nonexistent.pdf',
        ]);

        $response = $this->actingAs($user)->get(route('contracts.download', $contract));

        $response->assertStatus(404);
    }

    public function test_contract_download_returns_404_if_no_pdf_path(): void
    {
        $user = User::factory()->create();
        $contract = Contract::factory()->create([
            'pdf_path' => null,
        ]);

        $response = $this->actingAs($user)->get(route('contracts.download', $contract));

        $response->assertStatus(404);
    }

    public function test_regenerate_contract_deletes_old_pdf_and_creates_new(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $client = Client::factory()->create([
            'name' => 'Juan Garcia',
            'dni' => '12345678A',
            'address' => 'Calle Mayor 123, Vigo',
            'phone' => '600123456',
            'email' => 'juan@test.com',
        ]);
        $vehicle = Vehicle::factory()->create([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'plate' => '1234ABC',
        ]);
        $reservation = Reservation::factory()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'start_mileage' => 50000,
        ]);

        $oldPdfPath = 'contracts/old_contract.pdf';
        Storage::disk('public')->put($oldPdfPath, 'old pdf content');

        $contract = Contract::factory()->create([
            'reservation_id' => $reservation->id,
            'pdf_path' => $oldPdfPath,
            'generated_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        \Livewire\Livewire::test('pages::reservations.show', ['reservation' => $reservation])
            ->call('openRegenerateModal')
            ->assertSet('isRegenerating', true)
            ->set('combustible', '1/2')
            ->call('regenerateContract')
            ->assertSet('isRegenerating', false);

        Storage::disk('public')->assertMissing($oldPdfPath);

        $contract->refresh();
        $this->assertNotEquals($oldPdfPath, $contract->pdf_path);
        Storage::disk('public')->assertExists($contract->pdf_path);
    }

    public function test_regenerate_contract_does_nothing_without_existing_contract(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($user);

        \Livewire\Livewire::test('pages::reservations.show', ['reservation' => $reservation])
            ->set('isRegenerating', true)
            ->call('regenerateContract');

        $this->assertNull($reservation->fresh()->contract);
    }

    public function test_generate_pdf_creates_file_in_storage(): void
    {
        Storage::fake('public');

        $client = Client::factory()->create([
            'name' => 'Juan Garcia',
            'dni' => '12345678A',
            'address' => 'Calle Mayor 123, Vigo',
            'phone' => '600123456',
            'email' => 'juan@test.com',
        ]);
        $vehicle = Vehicle::factory()->create([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'plate' => '1234ABC',
        ]);
        $reservation = Reservation::factory()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'start_mileage' => 50000,
        ]);

        $path = ContractDownloadController::generatePdf($reservation);

        $this->assertNotEmpty($path);
        $this->assertStringStartsWith('contracts/', $path);
        Storage::disk('public')->assertExists($path);
    }
}
