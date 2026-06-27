<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Organization;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'subject'         => fake()->sentence(6),
            'description'     => fake()->paragraph(),
            'status'          => fake()->randomElement(['open', 'pending', 'resolved', 'closed']),
            'priority'        => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'requester_id'    => User::factory(),
            'assignee_id'     => User::factory(),
            'tags'            => fake()->randomElements(
                ['billing', 'bug', 'feature', 'urgent', 'v2'],
                fake()->numberBetween(0, 3)
            ),
        ];
    }
}
