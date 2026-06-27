<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Organization;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        $ticket = Ticket::factory()->create();
        $org = Organization::findOrFail($ticket->organization_id);

        return [
            'organization_id' => $org->id,
            'ticket_id'       => $ticket->id,
            'author_id'       => User::factory()->create(['organization_id' => $org->id])->id,
            'body'            => fake()->paragraph(),
            'is_internal'     => fake()->boolean(20),
        ];
    }
}
