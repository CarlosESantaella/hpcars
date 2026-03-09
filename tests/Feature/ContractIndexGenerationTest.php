<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ContractIndexGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contracts_index_shows_generate_button_when_reservations_without_contract_exist(): void
    {
        $user = User::factory()->create();
        Reservation::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::contracts.index')
            ->assertSee('Generar Contrato');
    }

    public function test_contracts_index_hides_generate_button_when_all_reservations_have_contracts(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();
        Contract::factory()->create([
            'reservation_id' => $reservation->id,
            'pdf_path' => 'contracts/test.pdf',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::contracts.index')
            ->assertDontSee('Generar Contrato');
    }

    public function test_generate_contract_from_contracts_index_requires_reservation_selection(): void
    {
        $user = User::factory()->create();
        Reservation::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::contracts.index')
            ->call('generateContract')
            ->assertHasErrors(['selectedReservationId']);
    }

    public function test_generate_contract_from_contracts_index_creates_contract(): void
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
            'start_mileage' => 50000,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::contracts.index')
            ->set('selectedReservationId', $reservation->id)
            ->set('combustible', '3/4')
            ->call('generateContract')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contracts', [
            'reservation_id' => $reservation->id,
        ]);

        $contract = Contract::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($contract->pdf_path);
        Storage::disk('public')->assertExists($contract->pdf_path);
    }

    public function test_generate_contract_skips_if_reservation_already_has_contract(): void
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
        Contract::factory()->create([
            'reservation_id' => $reservation->id,
            'pdf_path' => 'contracts/existing.pdf',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::contracts.index')
            ->set('selectedReservationId', $reservation->id)
            ->call('generateContract');

        $this->assertCount(1, Contract::where('reservation_id', $reservation->id)->get());
    }

    public function test_available_reservations_excludes_those_with_contracts(): void
    {
        $user = User::factory()->create();

        $reservationWithContract = Reservation::factory()->create();
        Contract::factory()->create([
            'reservation_id' => $reservationWithContract->id,
            'pdf_path' => 'contracts/test.pdf',
        ]);

        $reservationWithoutContract = Reservation::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test('pages::contracts.index');
        $availableIds = collect($component->instance()->availableReservations)->pluck('id')->all();

        $this->assertContains($reservationWithoutContract->id, $availableIds);
        $this->assertNotContains($reservationWithContract->id, $availableIds);
    }

    public function test_regenerate_contract_from_contracts_index(): void
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

        Livewire::test('pages::contracts.index')
            ->call('openRegenerateModal', $contract->id)
            ->assertSet('regeneratingContractId', $contract->id)
            ->set('combustible', '1/2')
            ->call('regenerateContract');

        Storage::disk('public')->assertMissing($oldPdfPath);

        $contract->refresh();
        $this->assertNotEquals($oldPdfPath, $contract->pdf_path);
        Storage::disk('public')->assertExists($contract->pdf_path);
    }

    public function test_search_contracts_by_client_dni(): void
    {
        $user = User::factory()->create();
        $clientWithDni = Client::factory()->create(['dni' => '99887766Z']);
        $clientOther = Client::factory()->create(['dni' => '11223344A']);

        $reservationMatch = Reservation::factory()->create(['client_id' => $clientWithDni->id]);
        Contract::factory()->create([
            'reservation_id' => $reservationMatch->id,
            'pdf_path' => 'contracts/match.pdf',
            'generated_at' => now(),
        ]);

        $reservationNoMatch = Reservation::factory()->create(['client_id' => $clientOther->id]);
        Contract::factory()->create([
            'reservation_id' => $reservationNoMatch->id,
            'pdf_path' => 'contracts/nomatch.pdf',
            'generated_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::contracts.index')
            ->set('search', '99887766Z')
            ->assertSee($clientWithDni->name)
            ->assertDontSee($clientOther->name);
    }
}
