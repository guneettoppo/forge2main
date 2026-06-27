<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Org A: Acme Corp ---
        $acme = Organization::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme',
        ]);

        $acmeAdmin = User::create([
            'organization_id' => $acme->id,
            'name'            => 'Admin User',
            'email'           => 'admin@acme.test',
            'password'        => Hash::make('password'),
            'role'            => 'admin',
        ]);

        $acmeAgent1 = User::create([
            'organization_id' => $acme->id,
            'name'            => 'Agent Smith',
            'email'           => 'agent.smith@acme.test',
            'password'        => Hash::make('password'),
            'role'            => 'agent',
        ]);

        $acmeAgent2 = User::create([
            'organization_id' => $acme->id,
            'name'            => 'Agent Jones',
            'email'           => 'agent.jones@acme.test',
            'password'        => Hash::make('password'),
            'role'            => 'agent',
        ]);

        $acmeCustomer1 = User::create([
            'organization_id' => $acme->id,
            'name'            => 'Customer Alice',
            'email'           => 'customer.alice@acme.test',
            'password'        => Hash::make('password'),
            'role'            => 'customer',
        ]);

        $acmeCustomer2 = User::create([
            'organization_id' => $acme->id,
            'name'            => 'Customer Bob',
            'email'           => 'customer.bob@acme.test',
            'password'        => Hash::make('password'),
            'role'            => 'customer',
        ]);

        $requesters = [$acmeCustomer1->id, $acmeCustomer2->id, $acmeAdmin->id];
        $assignees = [$acmeAgent1->id, $acmeAgent2->id, null];

        $statuses  = ['open', 'pending', 'resolved', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        // Create ~12 Acme tickets
        $acmeTickets = [];
        for ($i = 1; $i <= 12; $i++) {
            $status = $statuses[array_rand($statuses)];
            $ticket = Ticket::create([
                'organization_id' => $acme->id,
                'subject'         => "[Acme] Support issue #{$i} - " . fake()->sentence(4),
                'description'     => fake()->paragraph(),
                'status'          => $status,
                'priority'        => $priorities[array_rand($priorities)],
                'requester_id'    => $requesters[array_rand($requesters)],
                'assignee_id'     => $assignees[array_rand($assignees)],
                'tags'            => fake()->randomElements(['billing','bug','feature','v2'], fake()->numberBetween(0, 2)),
                'resolved_at'     => in_array($status, ['resolved', 'closed']) ? fake()->dateTimeBetween('-7 days', 'now') : null,
                'closed_at'       => $status === 'closed' ? fake()->dateTimeBetween('-3 days', 'now') : null,
            ]);
            $acmeTickets[] = $ticket;
        }

        // Add ~8 comments across Acme tickets
        foreach ($acmeTickets as $ticket) {
            $count = fake()->numberBetween(1, 4);
            $authors = [$acmeCustomer1->id, $acmeCustomer2->id, $acmeAdmin->id, $acmeAgent1->id, $acmeAgent2->id];
            for ($j = 0; $j < $count; $j++) {
                $authorId = $authors[array_rand($authors)];
                $isInternal = in_array($authorId, [$acmeAgent1->id, $acmeAgent2->id, $acmeAdmin->id])
                    && fake()->boolean(30);

                Comment::create([
                    'organization_id' => $acme->id,
                    'ticket_id'       => $ticket->id,
                    'author_id'       => $authorId,
                    'body'            => fake()->sentence(),
                    'is_internal'     => $isInternal,
                ]);
            }
        }

        // --- Org B: Globex Inc (isolation test data) ---
        $globex = Organization::create([
            'name' => 'Globex Inc',
            'slug' => 'globex',
        ]);

        $globexAdmin = User::create([
            'organization_id' => $globex->id,
            'name'            => 'Globex Admin',
            'email'           => 'admin@globex.test',
            'password'        => Hash::make('password'),
            'role'            => 'admin',
        ]);

        $globexCustomer = User::create([
            'organization_id' => $globex->id,
            'name'            => 'Globex Customer',
            'email'           => 'customer@globex.test',
            'password'        => Hash::make('password'),
            'role'            => 'customer',
        ]);

        // 2 Globex tickets — must NEVER appear in Acme queries
        foreach (range(1, 2) as $n) {
            Ticket::create([
                'organization_id' => $globex->id,
                'subject'         => "[Globex] Private ticket #{$n}",
                'description'     => fake()->paragraph(),
                'status'          => 'open',
                'priority'        => 'high',
                'requester_id'    => $globexCustomer->id,
                'assignee_id'     => null,
                'tags'            => ['private'],
            ]);
        }

        $this->command->info('Demo data seeded: Acme(12 tickets + comments), Globex(2 tickets, isolation control).');
    }
}
