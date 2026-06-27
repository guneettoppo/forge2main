<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;

class CommentPolicy
{
    public function viewInternal(User $user, Comment $comment): bool
    {
        // Internal notes only for agents and admins
        return $user->role === 'admin' || $user->role === 'agent';
    }

    public function create(User $user): bool
    {
        // Any authenticated user in the org can post comments
        return (bool) $user->organization_id;
    }

    public function update(User $user, Comment $comment): bool
    {
        // Only the author (or admin) can update their comment
        return $user->id === $comment->author_id || $user->role === 'admin';
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->role === 'admin' || $user->id === $comment->author_id;
    }
}
