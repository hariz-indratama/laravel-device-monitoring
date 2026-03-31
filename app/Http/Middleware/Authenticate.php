<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Only redirect for non-API (non-XHR) requests
        // API requests should return 401 JSON instead
        if ($request->expectsJson()) {
            return null;
        }

        return route('login');
    }

    /**
     * Handle unauthenticated requests — override to store intended URL in session.
     */
    protected function unauthenticated($request, array $guards): void
    {
        // Store intended URL so we can redirect back after login
        if (!$request->expectsJson() && empty($guards)) {
            session(['url.intended' => url()->current()]);
        }

        parent::unauthenticated($request, $guards);
    }
}
