<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $this->authorize('view', $ticket);

        $user = Auth::user();

        $query = Comment::where('ticket_id', $ticketId)
            ->orderBy('created_at', 'asc');

        // Hide internal notes from customers
        if ($user->role === 'customer') {
            $query->where('is_internal', false);
        }

        return \App\Http\Resources\CommentResource::collection($query->get());
    }

    public function store(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $this->authorize('view', $ticket);

        $user = Auth::user();

        // Customers shouldn't be able to post internal notes
        $validated = $request->validate([
            'body'        => ['required', 'string', 'min:1'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        if ($user->role === 'customer') {
            $validated['is_internal'] = false;
        }

        $comment = Comment::create([
            'organization_id' => $user->organization_id,
            'ticket_id'       => $ticket->id,
            'author_id'       => $user->id,
            'body'            => $validated['body'],
            'is_internal'     => $validated['is_internal'],
        ]);

        return (new \App\Http\Resources\CommentResource($comment->load('author')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, $ticketId, $commentId)
    {
        $comment = Comment::where('ticket_id', $ticketId)->findOrFail($commentId);

        $this->authorize('update', $comment);

        $validated = $request->validate([
            'body'        => ['required', 'string', 'min:1'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $comment->update($validated);

        return new \App\Http\Resources\CommentResource($comment->fresh('author'));
    }

    public function destroy($ticketId, $commentId)
    {
        $comment = Comment::where('ticket_id', $ticketId)->findOrFail($commentId);

        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json(null, 204);
    }
}
