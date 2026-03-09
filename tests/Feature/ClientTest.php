<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_page_requires_authentication(): void
    {
        $response = $this->get(route('clients.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_clients_list(): void
    {
        $user = User::factory()->create();
        Client::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('clients.index'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_create_client_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('clients.create'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_client_details(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($user)->get(route('clients.show', $client));

        $response->assertStatus(200);
    }

    public function test_client_factory_creates_valid_client(): void
    {
        $client = Client::factory()->create();

        $this->assertNotNull($client->name);
        $this->assertNotNull($client->email);
        $this->assertNotNull($client->phone);
    }

    public function test_client_has_reservations_relationship(): void
    {
        $client = Client::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $client->reservations);
    }
}
