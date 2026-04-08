<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Repository interface → Eloquent implementation bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        \App\Repositories\Contracts\EmployeeRepositoryInterface::class
            => \App\Repositories\Eloquent\EloquentEmployeeRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Super Admin — барча permission ларга автоматик рухсат
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
