<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_route_is_disabled(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('register'));
    }

    public function test_registration_endpoint_returns_not_found(): void
    {
        $response = $this->get('/register');

        $response->assertNotFound();
    }
}
