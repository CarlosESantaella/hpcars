<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ClientDocumentImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_client_with_document_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::clients.create')
            ->set('name', 'John Doe')
            ->set('dni', '12345678A')
            ->set('license_number', 'B-12345678')
            ->set('dni_image', UploadedFile::fake()->image('dni.jpg'))
            ->set('license_image', UploadedFile::fake()->image('license.jpg'))
            ->call('save');

        $client = Client::first();
        $this->assertNotNull($client);
        $this->assertNotNull($client->dni_image_path);
        $this->assertNotNull($client->license_image_path);
        Storage::disk('public')->assertExists($client->dni_image_path);
        Storage::disk('public')->assertExists($client->license_image_path);
    }

    public function test_can_create_client_without_document_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::clients.create')
            ->set('name', 'John Doe')
            ->call('save');

        $client = Client::first();
        $this->assertNotNull($client);
        $this->assertNull($client->dni_image_path);
        $this->assertNull($client->license_image_path);
    }

    public function test_document_image_must_be_an_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::clients.create')
            ->set('name', 'John Doe')
            ->set('dni_image', UploadedFile::fake()->create('document.pdf', 100))
            ->call('save')
            ->assertHasErrors('dni_image');
    }

    public function test_document_image_max_size_is_5mb(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::clients.create')
            ->set('name', 'John Doe')
            ->set('dni_image', UploadedFile::fake()->image('dni.jpg')->size(6000))
            ->call('save')
            ->assertHasErrors('dni_image');
    }

    public function test_can_update_client_with_new_document_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::clients.edit', ['client' => $client])
            ->set('dni_image', UploadedFile::fake()->image('new_dni.jpg'))
            ->set('license_image', UploadedFile::fake()->image('new_license.jpg'))
            ->call('save');

        $client->refresh();
        $this->assertNotNull($client->dni_image_path);
        $this->assertNotNull($client->license_image_path);
        Storage::disk('public')->assertExists($client->dni_image_path);
        Storage::disk('public')->assertExists($client->license_image_path);
    }

    public function test_replacing_image_deletes_old_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $oldPath = UploadedFile::fake()->image('old_dni.jpg')->store('clients/dni', 'public');
        $client = Client::factory()->create(['dni_image_path' => $oldPath]);

        $this->actingAs($user);

        Livewire::test('pages::clients.edit', ['client' => $client])
            ->set('dni_image', UploadedFile::fake()->image('new_dni.jpg'))
            ->call('save');

        Storage::disk('public')->assertMissing($oldPath);

        $client->refresh();
        $this->assertNotNull($client->dni_image_path);
        $this->assertNotEquals($oldPath, $client->dni_image_path);
        Storage::disk('public')->assertExists($client->dni_image_path);
    }

    public function test_can_delete_existing_dni_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $path = UploadedFile::fake()->image('dni.jpg')->store('clients/dni', 'public');
        $client = Client::factory()->create(['dni_image_path' => $path]);

        $this->actingAs($user);

        Livewire::test('pages::clients.edit', ['client' => $client])
            ->call('deleteExistingDniImage');

        Storage::disk('public')->assertMissing($path);
        $client->refresh();
        $this->assertNull($client->dni_image_path);
    }

    public function test_can_delete_existing_license_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $path = UploadedFile::fake()->image('license.jpg')->store('clients/licenses', 'public');
        $client = Client::factory()->create(['license_image_path' => $path]);

        $this->actingAs($user);

        Livewire::test('pages::clients.edit', ['client' => $client])
            ->call('deleteExistingLicenseImage');

        Storage::disk('public')->assertMissing($path);
        $client->refresh();
        $this->assertNull($client->license_image_path);
    }

    public function test_client_model_returns_image_urls(): void
    {
        $client = Client::factory()->create([
            'dni_image_path' => 'clients/dni/test.jpg',
            'license_image_path' => 'clients/licenses/test.jpg',
        ]);

        $this->assertNotNull($client->dniImageUrl());
        $this->assertNotNull($client->licenseImageUrl());
        $this->assertStringContainsString('clients/dni/test.jpg', $client->dniImageUrl());
        $this->assertStringContainsString('clients/licenses/test.jpg', $client->licenseImageUrl());
    }

    public function test_client_model_returns_null_urls_when_no_images(): void
    {
        $client = Client::factory()->create([
            'dni_image_path' => null,
            'license_image_path' => null,
        ]);

        $this->assertNull($client->dniImageUrl());
        $this->assertNull($client->licenseImageUrl());
    }

    public function test_show_page_displays_document_images(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'dni_image_path' => 'clients/dni/test.jpg',
            'license_image_path' => 'clients/licenses/test.jpg',
        ]);

        $response = $this->actingAs($user)->get(route('clients.show', $client));

        $response->assertStatus(200);
        $response->assertSee('Imágenes de documentos');
    }
}
