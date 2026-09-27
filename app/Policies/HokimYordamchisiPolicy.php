<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HokimYordamchisi;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAccess;

class HokimYordamchisiPolicy
{
    use ChecksTenantAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hokim-yordamchilari.view');
    }

    public function view(User $user, HokimYordamchisi $hy): bool
    {
        return $user->hasPermissionTo('hokim-yordamchilari.view') && $this->sameTenant($user, $hy);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hokim-yordamchilari.create');
    }

    public function update(User $user, HokimYordamchisi $hy): bool
    {
        return $user->hasPermissionTo('hokim-yordamchilari.update') && $this->sameTenant($user, $hy);
    }

    public function delete(User $user, HokimYordamchisi $hy): bool
    {
        return $user->hasPermissionTo('hokim-yordamchilari.delete') && $this->sameTenant($user, $hy);
    }
}
