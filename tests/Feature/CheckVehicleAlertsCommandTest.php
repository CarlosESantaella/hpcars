<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckVehicleAlertsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_itv_notification_for_vehicle_due_tomorrow(): void
    {
        Vehicle::factory()->create([
            'itv_date' => now()->addDay(),
        ]);

        $this->artisan('app:check-vehicle-alerts')
            ->assertExitCode(0);

        $this->assertDatabaseHas('notifications', [
            'type' => NotificationType::Itv->value,
        ]);
    }

    public function test_command_creates_itv_notification_for_vehicle_due_today(): void
    {
        Vehicle::factory()->create([
            'itv_date' => now()->toDateString(),
        ]);

        $this->artisan('app:check-vehicle-alerts')
            ->assertExitCode(0);

        $this->assertDatabaseHas('notifications', [
            'type' => NotificationType::Itv->value,
        ]);
    }

    public function test_command_creates_maintenance_notification_for_vehicle_due_tomorrow(): void
    {
        Vehicle::factory()->create([
            'next_maintenance_date' => now()->addDay(),
        ]);

        $this->artisan('app:check-vehicle-alerts')
            ->assertExitCode(0);

        $this->assertDatabaseHas('notifications', [
            'type' => NotificationType::Maintenance->value,
        ]);
    }

    public function test_command_creates_maintenance_notification_for_vehicle_due_today(): void
    {
        Vehicle::factory()->create([
            'next_maintenance_date' => now()->toDateString(),
        ]);

        $this->artisan('app:check-vehicle-alerts')
            ->assertExitCode(0);

        $this->assertDatabaseHas('notifications', [
            'type' => NotificationType::Maintenance->value,
        ]);
    }

    public function test_command_does_not_create_notification_for_distant_dates(): void
    {
        Vehicle::factory()->create([
            'itv_date' => now()->addDays(10),
            'next_maintenance_date' => now()->addDays(10),
        ]);

        $this->artisan('app:check-vehicle-alerts')
            ->assertExitCode(0);

        $this->assertEquals(0, Notification::count());
    }

    public function test_command_does_not_create_notification_for_past_dates(): void
    {
        Vehicle::factory()->create([
            'itv_date' => now()->subDay(),
            'next_maintenance_date' => now()->subDay(),
        ]);

        $this->artisan('app:check-vehicle-alerts')
            ->assertExitCode(0);

        $this->assertEquals(0, Notification::count());
    }

    public function test_command_does_not_duplicate_notifications(): void
    {
        $vehicle = Vehicle::factory()->create([
            'itv_date' => now()->addDay(),
        ]);

        $this->artisan('app:check-vehicle-alerts')->assertExitCode(0);
        $this->artisan('app:check-vehicle-alerts')->assertExitCode(0);

        $this->assertEquals(1, Notification::where('type', NotificationType::Itv)->count());
    }

    public function test_command_creates_both_itv_and_maintenance_notifications(): void
    {
        Vehicle::factory()->create([
            'itv_date' => now()->addDay(),
            'next_maintenance_date' => now()->toDateString(),
        ]);

        $this->artisan('app:check-vehicle-alerts')
            ->assertExitCode(0);

        $this->assertEquals(1, Notification::where('type', NotificationType::Itv)->count());
        $this->assertEquals(1, Notification::where('type', NotificationType::Maintenance)->count());
    }

    public function test_command_handles_null_dates(): void
    {
        Vehicle::factory()->create([
            'itv_date' => null,
            'next_maintenance_date' => null,
        ]);

        $this->artisan('app:check-vehicle-alerts')
            ->assertExitCode(0);

        $this->assertEquals(0, Notification::count());
    }

    public function test_notification_message_includes_vehicle_info(): void
    {
        $vehicle = Vehicle::factory()->create([
            'plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'itv_date' => now()->addDay(),
        ]);

        $this->artisan('app:check-vehicle-alerts')->assertExitCode(0);

        $notification = Notification::first();
        $this->assertStringContainsString('ABC-1234', $notification->message);
        $this->assertStringContainsString('Toyota Corolla', $notification->message);
    }
}
