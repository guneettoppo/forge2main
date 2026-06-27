<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrganization
{
    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            $user = Auth::user();

            if ($user && filled($user->organization_id)) {
                $builder->where($builder->getQuery()->from . '.organization_id', $user->organization_id);
            }
        });
    }
}
