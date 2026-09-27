<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\YouthMeeting;
use App\Policies\Concerns\ChecksTenantAccess;

class YouthMeetingPolicy
{
    use ChecksTenantAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('meetings.view');
    }

    public function view(User $user, YouthMeeting $meeting): bool
    {
        return $user->hasPermissionTo('meetings.view') && $this->sameTenant($user, $meeting);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('meetings.create');
    }

    public function update(User $user, YouthMeeting $meeting): bool
    {
        return $user->hasPermissionTo('meetings.update') && $this->sameTenant($user, $meeting);
    }

    public function delete(User $user, YouthMeeting $meeting): bool
    {
        return $user->hasPermissionTo('meetings.delete') && $this->sameTenant($user, $meeting);
    }
}
