<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Inertia root template.
     */
    protected $rootView = 'app';

    /**
     * Ҳар бир Inertia жавобга қўшиладиган умумий маълумотлар.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'roles' => fn () => $request->user()?->getRoleNames() ?? [],
                'permissions' => fn () => $request->user()?->getAllPermissions()->pluck('name') ?? [],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
