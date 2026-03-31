<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerRole
{
    /**
     * Only allow users with the 'owner' role to proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isOwner()) {
            abort(403, 'Only owners can access this resource.');
        }

        return $next($request);
    }
}
