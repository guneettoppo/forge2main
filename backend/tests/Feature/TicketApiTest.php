<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string { return $user->createToken('test')->plainTextToken; }
    private function org($name='Acme',$slug='acme'): Organization { return Organization::create(compact('name','slug')); }
    private function admin(): User { return User::create(['organization_id'=>$this->org()->id,'name'=>'A','email'=>'a@t.test','password'=>bcrypt('password'),'role'=>'admin']); }
    private function customer(): User { $o=$this->org('C','c'); return User::create(['organization_id'=>$o->id,'name'=>'C','email'=>'c@t.test','password'=>bcrypt('password'),'role'=>'customer']); }

    public function test_admin_can_create_ticket(): void
    {
        $admin = $this->admin();
        $res = $this->withToken($this->token($admin))->postJson('/api/tickets', ['subject'=>'Cannot login','priority'=>'high','tags'=>['bug']]);
        $res->assertStatus(201)->assertJson(['subject'=>'Cannot login','priority'=>'high','status'=>'open']);
        $this->assertDatabaseHas('tickets', ['subject'=>'Cannot login']);
    }

    public function test_customer_sees_only_own_tickets(): void
    {
        $org = $this->org('A','a');
        $customer = User::create(['organization_id'=>$org->id,'name'=>'C','email'=>'c@t.test','password'=>bcrypt('password'),'role'=>'customer']);
        $agent = User::create(['organization_id'=>$org->id,'name'=>'A','email'=>'a@t.test','password'=>bcrypt('password'),'role'=>'agent']);
        $token = $this->token($customer);
        Ticket::create(['organization_id'=>$org->id,'requester_id'=>$customer->id,'subject'=>'My ticket','priority'=>'low']);
        Ticket::create(['organization_id'=>$org->id,'requester_id'=>$agent->id,'subject'=>'Agent ticket','priority'=>'low']);

        $res = $this->withToken($token)->getJson('/api/tickets');
        $this->assertCount(1, $res->json('data'));
        $this->assertEquals('My ticket', $res->json('data.0.subject'));
    }

    public function test_cross_org_ticket_is_404(): void
    {
        $orgA = $this->org('A','a');
        $orgB = $this->org('B','b');
        $adminA = User::create(['organization_id'=>$orgA->id,'name'=>'A','email'=>'a@a.test','password'=>bcrypt('password'),'role'=>'admin']);
        $ticket = Ticket::create(['organization_id'=>$orgB->id,'subject'=>'Globb','priority'=>'low','requester_id'=>$adminA->id]);
        $this->withToken($this->token($adminA))->getJson("/api/tickets/{$ticket->id}")->assertStatus(404);
    }

    public function test_admin_can_update_and_delete_ticket(): void
    {
        $admin = $this->admin();
        $ticket = Ticket::create(['organization_id'=>$admin->organization_id,'subject'=>'Up','priority'=>'low','requester_id'=>$admin->id]);
        $this->withToken($this->token($admin))->putJson("/api/tickets/{$ticket->id}", ['status'=>'resolved'])->assertStatus(200)->assertJson(['status'=>'resolved']);
        $this->withToken($this->token($admin))->deleteJson("/api/tickets/{$ticket->id}")->assertStatus(204);
        $this->assertModelMissing($ticket);
    }

    public function test_search_and_filters(): void
    {
        $admin = $this->admin();
        $token = $this->token($admin);
        Ticket::create(['organization_id'=>$admin->organization_id,'subject'=>'Payment failed','description'=>'Card error','priority'=>'high','requester_id'=>$admin->id]);
        Ticket::create(['organization_id'=>$admin->organization_id,'subject'=>'Login issue','description'=>'Page broken','priority'=>'low','requester_id'=>$admin->id]);
        $this->withToken($token)->getJson('/api/tickets?q=payment')->assertJsonCount(1, 'data');
        $this->withToken($token)->getJson('/api/tickets?priority=low')->assertJsonCount(1, 'data');
    }
}
