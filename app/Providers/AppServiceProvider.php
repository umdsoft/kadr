<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Eloquent\EloquentEmployeeRepository;
use App\Support\Tenant\TenantContext;
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
        EmployeeRepositoryInterface::class => EloquentEmployeeRepository::class,
    ];

    public function register(): void
    {
        // Tenant context request davomida bitta instance bo'ladi
        $this->app->scoped(TenantContext::class);
    }

    public function boot(): void
    {
        // Super Admin — барча permission ларга автоматик рухсат
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
