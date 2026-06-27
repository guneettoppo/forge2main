<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToOrganization;

class Ticket extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'subject',
        'description',
        'status',
        'priority',
        'requester_id',
        'assignee_id',
        'tags',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id')->withDefault([
            'name' => 'Unassigned',
        ]);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
