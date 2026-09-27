<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MahallaCouncil;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAccess;

class MahallaCouncilPolicy
{
    use ChecksTenantAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('councils.view');
    }

    public function view(User $user, MahallaCouncil $council): bool
    {
        return $user->hasPermissionTo('councils.view') && $this->sameTenant($user, $council);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('councils.manage');
    }

    public function update(User $user, MahallaCouncil $council): bool
    {
        return $user->hasPermissionTo('councils.manage') && $this->sameTenant($user, $council);
    }

    public function delete(User $user, MahallaCouncil $council): bool
    {
        return $user->hasPermissionTo('councils.manage') && $this->sameTenant($user, $council);
    }
}
