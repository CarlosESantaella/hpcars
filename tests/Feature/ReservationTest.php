<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservations_page_requires_authentication(): void
    {
        $response = $this->get(route('reservations.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_reservations_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.index'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_create_reservation_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.create'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_reservation_details(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.show', $reservation));

        $response->assertStatus(200);
    }

    public function test_reservation_factory_creates_valid_reservation(): void
    {
        $reservation = Reservation::factory()->create();

        $this->assertNotNull($reservation->client_id);
        $this->assertNotNull($reservation->vehicle_id);
        $this->assertNotNull($reservation->start_date);
        $this->assertNotNull($reservation->end_date);
        $this->assertEquals(ReservationStatus::Pending, $reservation->status);
    }

    public function test_reservation_belongs_to_client(): void
    {
        $client = Client::factory()->create();
        $reservation = Reservation::factory()->for($client)->create();

        $this->assertEquals($client->id, $reservation->client->id);
    }

    public function test_reservation_belongs_to_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();
        $reservation = Reservation::factory()->for($vehicle)->create();

        $this->assertEquals($vehicle->id, $reservation->vehicle->id);
    }

    public function test_reservation_active_scope_returns_only_active_reservations(): void
    {
        Reservation::factory()->active()->count(2)->create();
        Reservation::factory()->completed()->count(3)->create();

        $activeReservations = Reservation::active()->get();

        $this->assertCount(2, $activeReservations);
    }

    public function test_reservation_end_date_is_after_start_date(): void
    {
        $reservation = Reservation::factory()->create();

        $this->assertTrue($reservation->end_date->gte($reservation->start_date));
    }
}
