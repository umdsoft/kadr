<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAccess;

class EmployeePolicy
{
    use ChecksTenantAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('kadrlar.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('kadrlar.view') && $this->sameTenant($user, $employee);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('kadrlar.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('kadrlar.update') && $this->sameTenant($user, $employee);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('kadrlar.delete') && $this->sameTenant($user, $employee);
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('kadrlar.delete') && $this->sameTenant($user, $employee);
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('kadrlar.delete') && $this->sameTenant($user, $employee);
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('kadrlar.export');
    }
}
