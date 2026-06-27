<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $org = Organization::create(['name' => 'TestCo', 'slug' => 'testco']);
        $user = User::create([
            'organization_id' => $org->id,
            'name'            => 'Admin',
            'email'           => 'admin@testco.test',
            'password'        => bcrypt('password'),
            'role'            => 'admin',
        ]);
        return $user;
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_admin_can_create_ticket(): void
    {
        $user = $this->actingAsAdmin();
        $token = $this->tokenFor($user);

        $response = $this->withToken($token)->postJson('/api/tickets', [
            'subject'     => 'Cannot login',
            'description' => 'Getting 500 error',
            'priority'    => 'high',
            'tags'        => ['bug', 'urgent'],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'subject', 'status', 'priority', 'requester', 'assignee',
            ])
            ->assertJson([
                'subject'   => 'Cannot login',
                'priority'  => 'high',
                'status'    => 'open',
                'requester' => ['id' => $user->id, 'name' => 'Admin'],
            ]);

        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_customer_can_create_ticket_without_assignee(): void
    {
        $org = Organization::create(['name' => 'B', 'slug' => 'b']);
        $customer = User::create([
            'organization_id' => $org->id,
            'name'            => 'Customer B',
            'email'           => 'b@test.test',
            'password'        => bcrypt('password'),
            'role'            => 'customer',
        ]);
        $token = $this->tokenFor($customer);

        $response = $this->withToken($token)->postJson('/api/tickets', [
            'subject'    => 'Help me',
            'description' => 'Need support',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tickets', [
            'requester_id' => $customer->id,
            'assignee_id'  => null,
        ]);
    }

    public function test_ticket_list_filters_by_status(): void
    {
        $user = $this->actingAsAdmin();
        $token = $this->tokenFor($user);

        Ticket::create([
            'organization_id' => $user->organization_id,
            'subject'         => 'Open issue',
            'status'          => 'open',
            'priority'        => 'medium',
            'requester_id'    => $user->id,
        ]);
        Ticket::create([
            'organization_id' => $user->organization_id,
            'subject'         => 'Resolved issue',
            'status'          => 'resolved',
            'priority'        => 'low',
            'requester_id'    => $user->id,
        ]);

        $response = $this->withToken($token)->getJson('/api/tickets?status=resolved');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_customer_sees_only_own_tickets(): void
    {
        $org = Organization::create(['name' => 'C', 'slug' => 'c']);
        $customer = User::create([
            'organization_id' => $org->id,
            'name'            => 'Cust C',
            'email'           => 'c@test.test',
            'password'        => bcrypt('password'),
            'role'            => 'customer',
        ]);
        $agent = User::create([
            'organization_id' => $org->id,
            'name'            => 'Agent C',
            'email'           => 'agc@test.test',
            'password'        => bcrypt('password'),
            'role'            => 'agent',
        ]);
        $token = $this->tokenFor($customer);

        Ticket::create([
            'organization_id' => $org->id,
            'subject'         => 'My ticket',
            'requester_id'    => $customer->id,
            'priority'        => 'low',
        ]);
        Ticket::create([
            'organization_id' => $org->id,
            'subject'         => 'Agent ticket',
            'requester_id'    => $agent->id,
            'priority'        => 'low',
        ]);

        $response = $this->withToken($token)->getJson('/api/tickets');
        $ticketSubjects = collect($response->json('data'))->pluck('subject')->all();

        $this->assertCount(1, $response->json('data'));
        $this->assertContains('My ticket', $ticketSubjects);
    }

    public function test_admin_can_update_ticket_status_and_assignee(): void
    {
        $user = $this->actingAsAdmin();
        $token = $this->tokenFor($user);

        $ticket = Ticket::create([
            'organization_id' => $user->organization_id,
            'subject'         => 'Test ticket',
            'status'          => 'open',
            'priority'        => 'medium',
            'requester_id'    => $user->id,
        ]);

        $response = $this->withToken($token)->putJson("/api/tickets/{$ticket->id}", [
            'status'      => 'resolved',
            'assignee_id' => $user->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'      => 'resolved',
                'assignee_id' => $user->id,
            ]);

        $this->assertNotNull($ticket->refresh()->resolved_at);
    }

    public function test_admin_can_delete_ticket(): void
    {
        $user = $this->actingAsAdmin();
        $token = $this->tokenFor($user);

        $ticket = Ticket::create([
            'organization_id' => $user->organization_id,
            'subject'         => 'Delete me',
            'status'          => 'open',
            'priority'        => 'low',
            'requester_id'    => $user->id,
        ]);

        $this->withToken($token)->deleteJson("/api/tickets/{$ticket->id}")
            ->assertStatus(204);

        $this->assertModelMissing($ticket);
    }

    public function test_search_filters_by_subject_and_description(): void
    {
        $user = $this->actingAsAdmin();
        $token = $this->tokenFor($user);

        Ticket::create([
            'organization_id' => $user->organization_id,
            'subject'         => 'Payment failed on checkout',
            'description'     => 'Receiving card error',
            'status'          => 'open',
            'priority'        => 'high',
            'requester_id'    => $user->id,
        ]);
        Ticket::create([
            'organization_id' => $user->organization_id,
            'subject'         => 'Login issue',
            'description'     => 'Payment page looks broken',
            'status'          => 'open',
            'priority'        => 'high',
            'requester_id'    => $user->id,
        ]);

        $response = $this->withToken($token)->getJson('/api/tickets?q=payment');
        $this->assertCount(1, $response->json('data'));
    }
}
