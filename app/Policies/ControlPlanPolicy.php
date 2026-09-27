<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ControlPlan;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAccess;

class ControlPlanPolicy
{
    use ChecksTenantAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tadbirlar.view');
    }

    public function view(User $user, ControlPlan $plan): bool
    {
        return $user->hasPermissionTo('tadbirlar.view') && $this->sameTenant($user, $plan);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tadbirlar.create');
    }

    public function update(User $user, ControlPlan $plan): bool
    {
        if (! $user->hasPermissionTo('tadbirlar.update') || ! $this->sameTenant($user, $plan)) {
            return false;
        }

        return $plan->created_by === $user->id
            || $this->isCrossTenantAdmin($user)
            || $user->hasRole('tuman-admin');
    }

    public function delete(User $user, ControlPlan $plan): bool
    {
        if (! $user->hasPermissionTo('tadbirlar.delete') || ! $this->sameTenant($user, $plan)) {
            return false;
        }

        return $plan->created_by === $user->id
            || $this->isCrossTenantAdmin($user)
            || $user->hasRole('tuman-admin');
    }
}
