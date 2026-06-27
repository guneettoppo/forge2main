<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Auth\Access\AuthorizationException;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        // Global scope ensures org isolation, but we also enforce role rules
        return $user->role === 'admin'
            || $user->role === 'agent'
            || $user->id === $ticket->requester_id;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        // Only agents/admins can update ticket details
        return $user->role === 'admin' || $user->role === 'agent';
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        // Only agents/admins can change assignee
        return $user->role === 'admin' || $user->role === 'agent';
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->role === 'admin';
    }
}
