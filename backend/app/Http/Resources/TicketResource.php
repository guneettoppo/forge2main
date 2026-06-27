<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'subject'       => $this->subject,
            'description'   => $this->description,
            'status'        => $this->status,
            'priority'      => $this->priority,
            'tags'          => $this->tags ?? [],
            'requester_id'  => $this->requester_id,
            'requester'     => $this->requester ? [
                'id'    => $this->requester->id,
                'name'  => $this->requester->name,
                'email' => $this->requester->email,
                'role'  => $this->requester->role,
            ] : null,
            'assignee_id'   => $this->assignee_id,
            'assignee'      => $this->assignee ? [
                'id'    => $this->assignee->id,
                'name'  => $this->assignee->name,
                'email' => $this->assignee->email,
                'role'  => $this->assignee->role,
            ] : null,
            'resolved_at'   => $this->resolved_at?->toIso8601String(),
            'closed_at'     => $this->closed_at?->toIso8601String(),
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
        ];
    }
}
