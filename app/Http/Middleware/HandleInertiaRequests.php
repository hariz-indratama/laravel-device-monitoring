<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'csrf_token' => fn () => csrf_token(),
            'auth'       => fn () => $request->user() ? [
                'id'    => $request->user()->id,
                'name'  => $request->user()->name,
                'email' => $request->user()->email,
                'role'  => $request->user()->role?->value,
            ] : null,
            // Intended URL stored by Authenticate middleware — used by Login page
            'intended'   => fn () => session('url.intended') ?? null,
            // Flash messages (success/error)
            'flash'      => fn () => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ];
    }
}
