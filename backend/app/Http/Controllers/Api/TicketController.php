<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Policies\TicketPolicy;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::with(['requester', 'assignee']);

        if ($user->role === 'customer') {
            $query->where('requester_id', $user->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }
        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->get('per_page', 20), 100);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return \App\Http\Resources\TicketResource::collection($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'      => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'priority'     => ['nullable', 'in:low,medium,high,urgent'],
            'tags'         => ['nullable', 'array'],
            'tags.*'       => ['string', 'max:50'],
            'assignee_id'  => ['nullable', 'exists:users,id'],
        ]);

        $user = Auth::user();

        // Auto-set requester to current user
        $validated['requester_id'] = $user->id;
        $validated['organization_id'] = $user->organization_id;
        if (empty($validated['priority'])) {
            $validated['priority'] = 'medium';
        }

        // For customers, assignee is ignored — they can't assign
        if ($user->role === 'customer' && !empty($validated['assignee_id'])) {
            unset($validated['assignee_id']);
        }

        $ticket = Ticket::create($validated);

        return (new \App\Http\Resources\TicketResource($ticket->load(['requester', 'assignee'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show($id)
    {
        $ticket = Ticket::with(['requester', 'assignee', 'comments.author'])
            ->findOrFail($id);

        $this->authorize('view', $ticket);

        return new \App\Http\Resources\TicketResource($ticket);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'subject'     => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status'      => ['sometimes', 'in:open,pending,resolved,closed'],
            'priority'    => ['sometimes', 'in:low,medium,high,urgent'],
            'tags'        => ['sometimes', 'nullable', 'array'],
            'tags.*'      => ['string', 'max:50'],
            'assignee_id' => ['nullable', 'exists:users,id'],
        ]);

        // Customers cannot change assignee or status
        $user = Auth::user();
        if ($user->role === 'customer') {
            unset($validated['assignee_id'], $validated['status']);
            // Customers can only update subject/description/tags
            $allowed = ['subject', 'description', 'tags'];
            $validated = array_intersect_key($validated, array_flip($allowed));
        }

        if (isset($validated['status'])) {
            if ($validated['status'] === 'resolved' && !$ticket->resolved_at) {
                $validated['resolved_at'] = now();
            }
            if ($validated['status'] === 'closed' && !$ticket->closed_at) {
                $validated['closed_at'] = now();
            }
        }

        $ticket->update($validated);

        return new \App\Http\Resources\TicketResource($ticket->fresh(['requester', 'assignee']));
    }

    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorize('delete', $ticket);
        $ticket->delete();

        return response()->json(null, 204);
    }
}
