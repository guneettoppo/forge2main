<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_201_with_token(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'register@example.com',
            'password' => 'secret123',
            'organization_name' => 'Acme',
            'organization_slug' => 'acme',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'role', 'organization_id'],
            ]);

        $this->assertDatabaseCount('organizations', 1);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_returns_200_with_token(): void
    {
        $organization = Organization::create(['name' => 'Acme', 'slug' => 'acme']);

        $user = User::create([
            'organization_id' => $organization->id,
            'name' => 'Login User',
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
            'role' => 'agent',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'role', 'organization_id'],
            ]);
    }

    public function test_login_with_bad_credentials_returns_unprocessable(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'bad@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422);
    }

    public function test_logout_revokes_token(): void
    {
        $organization = Organization::create(['name' => 'Acme', 'slug' => 'acme']);

        $user = User::create([
            'organization_id' => $organization->id,
            'name' => 'Logout User',
            'email' => 'logout@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertStatus(204);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
