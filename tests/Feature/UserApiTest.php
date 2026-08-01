<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_api_returns_users(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonPath('0.name', 'Test User')
            ->assertJsonPath('0.email', 'test@example.com');
    }
}
