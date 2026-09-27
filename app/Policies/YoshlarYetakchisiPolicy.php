<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\YoshlarYetakchisi;
use App\Policies\Concerns\ChecksTenantAccess;

class YoshlarYetakchisiPolicy
{
    use ChecksTenantAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('yoshlar.view');
    }

    public function view(User $user, YoshlarYetakchisi $yy): bool
    {
        return $user->hasPermissionTo('yoshlar.view') && $this->sameTenant($user, $yy);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('yoshlar.create');
    }

    public function update(User $user, YoshlarYetakchisi $yy): bool
    {
        return $user->hasPermissionTo('yoshlar.update') && $this->sameTenant($user, $yy);
    }

    public function delete(User $user, YoshlarYetakchisi $yy): bool
    {
        return $user->hasPermissionTo('yoshlar.delete') && $this->sameTenant($user, $yy);
    }
}
