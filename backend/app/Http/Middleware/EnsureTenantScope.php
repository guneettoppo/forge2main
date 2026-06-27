<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || empty($user->organization_id)) {
            abort(404);
        }

        $request->attributes->set('resolved_organization_id', (int) $user->organization_id);

        return $next($request);
    }
}
