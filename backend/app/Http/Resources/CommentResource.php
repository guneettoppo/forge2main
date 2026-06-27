<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'ticket_id'       => $this->ticket_id,
            'author_id'       => $this->author_id,
            'author'          => $this->author ? [
                'id'    => $this->author->id,
                'name'  => $this->author->name,
                'email' => $this->author->email,
                'role'  => $this->author->role,
            ] : null,
            'body'            => $this->body,
            'is_internal'     => (bool) $this->is_internal,
            'created_at'      => $this->created_at->toIso8601String(),
            'updated_at'      => $this->updated_at->toIso8601String(),
        ];
    }
}
