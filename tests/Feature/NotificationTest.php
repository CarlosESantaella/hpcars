<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_page_requires_authentication(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_notifications_list(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
    }

    public function test_notifications_page_shows_notifications(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $notification = Notification::factory()->itv()->create([
            'vehicle_id' => $vehicle->id,
            'message' => 'La ITV del vehículo vence mañana',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('La ITV del vehículo vence mañana');
    }

    public function test_notification_belongs_to_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();
        $notification = Notification::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertEquals($vehicle->id, $notification->vehicle->id);
    }

    public function test_unread_scope_filters_unread_notifications(): void
    {
        Notification::factory()->count(2)->create(['is_read' => false]);
        Notification::factory()->count(3)->read()->create();

        $this->assertCount(2, Notification::unread()->get());
    }

    public function test_notification_type_enum_has_correct_labels(): void
    {
        $this->assertEquals('ITV', NotificationType::Itv->label());
        $this->assertEquals('Mantenimiento', NotificationType::Maintenance->label());
    }

    public function test_notification_type_enum_has_correct_colors(): void
    {
        $this->assertEquals('amber', NotificationType::Itv->color());
        $this->assertEquals('blue', NotificationType::Maintenance->color());
    }

    public function test_notification_type_enum_has_correct_icons(): void
    {
        $this->assertEquals('exclamation-triangle', NotificationType::Itv->icon());
        $this->assertEquals('wrench-screwdriver', NotificationType::Maintenance->icon());
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['is_read' => false]);

        Livewire::actingAs($user)
            ->test('pages::notifications.index')
            ->call('markAsRead', $notification->id);

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_user_can_mark_notification_as_unread(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->read()->create();

        Livewire::actingAs($user)
            ->test('pages::notifications.index')
            ->call('markAsUnread', $notification->id);

        $this->assertFalse($notification->fresh()->is_read);
    }

    public function test_user_can_mark_all_as_read(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create(['is_read' => false]);

        Livewire::actingAs($user)
            ->test('pages::notifications.index')
            ->call('markAllAsRead');

        $this->assertEquals(0, Notification::unread()->count());
    }

    public function test_user_can_delete_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::notifications.index')
            ->call('deleteNotification', $notification->id);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_notifications_can_be_filtered_by_type(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(2)->itv()->create();
        Notification::factory()->count(3)->maintenance()->create();

        Livewire::actingAs($user)
            ->test('pages::notifications.index')
            ->set('type', 'itv')
            ->assertSee('ITV');
    }

    public function test_dashboard_shows_unread_notifications(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'is_read' => false,
            'title' => 'ITV próxima a vencer',
            'message' => 'Test notification message',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Test notification message');
    }

    public function test_dashboard_mark_as_read_works(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['is_read' => false]);

        Livewire::actingAs($user)
            ->test('pages::dashboard')
            ->call('markAsRead', $notification->id);

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_dashboard_mark_all_as_read_works(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create(['is_read' => false]);

        Livewire::actingAs($user)
            ->test('pages::dashboard')
            ->call('markAllAsRead');

        $this->assertEquals(0, Notification::unread()->count());
    }
}
