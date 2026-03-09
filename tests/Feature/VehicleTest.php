<?php

namespace Tests\Feature;

use App\Enums\VehicleStatus;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicles_page_requires_authentication(): void
    {
        $response = $this->get(route('vehicles.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_vehicles_list(): void
    {
        $user = User::factory()->create();
        Vehicle::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('vehicles.index'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_create_vehicle_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('vehicles.create'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_vehicle_details(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($user)->get(route('vehicles.show', $vehicle));

        $response->assertStatus(200);
    }

    public function test_vehicle_factory_creates_valid_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->assertNotNull($vehicle->plate);
        $this->assertNotNull($vehicle->brand);
        $this->assertNotNull($vehicle->model);
        $this->assertNotNull($vehicle->year);
        $this->assertEquals(VehicleStatus::Free, $vehicle->status);
    }

    public function test_vehicle_full_name_returns_brand_and_model(): void
    {
        $vehicle = Vehicle::factory()->create([
            'brand' => 'Seat',
            'model' => 'Ibiza',
        ]);

        $this->assertEquals('Seat Ibiza', $vehicle->fullName());
    }

    public function test_vehicle_is_available_when_status_is_free(): void
    {
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Free]);

        $this->assertTrue($vehicle->isAvailable());
    }

    public function test_vehicle_is_not_available_when_rented(): void
    {
        $vehicle = Vehicle::factory()->rented()->create();

        $this->assertFalse($vehicle->isAvailable());
    }

    public function test_vehicle_scope_free_returns_only_free_vehicles(): void
    {
        Vehicle::factory()->count(3)->create(['status' => VehicleStatus::Free]);
        Vehicle::factory()->count(2)->rented()->create();

        $freeVehicles = Vehicle::free()->get();

        $this->assertCount(3, $freeVehicles);
    }
}
