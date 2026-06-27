<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_scope_returns_only_authenticated_users_organization_records(): void
    {
        $orgA = Organization::create(['name' => 'Org A', 'slug' => 'org-a']);
        $orgB = Organization::create(['name' => 'Org B', 'slug' => 'org-b']);

        $userA = User::create([
            'organization_id' => $orgA->id,
            'name' => 'User A',
            'email' => 'a@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $userB = User::create([
            'organization_id' => $orgB->id,
            'name' => 'User B',
            'email' => 'b@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->actingAs($userA, 'sanctum');

        $users = User::all();

        $this->assertCount(1, $users);
        $this->assertTrue($users->contains($userA));
        $this->assertFalse($users->contains($userB));
    }
}
