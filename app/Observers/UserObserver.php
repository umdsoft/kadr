<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\Auth\CentralIdentitySync;

/**
 * KBT userdagi har o'zgarish markaziy auth.users'ga sinxronlanadi
 * (yaratish, parol/ism o'zgarishi, soft-delete, restore). pgsql'da faol.
 */
class UserObserver
{
    public function __construct(private readonly CentralIdentitySync $sync)
    {
    }

    public function saved(User $user): void
    {
        $this->sync->push($user);
    }

    public function deleted(User $user): void
    {
        if ($user->isForceDeleting()) {
            return; // forceDeleted() handler butunlay o'chiradi
        }

        $this->sync->push($user); // soft delete -> is_active=false
    }

    public function forceDeleted(User $user): void
    {
        $this->sync->remove($user);
    }

    public function restored(User $user): void
    {
        $this->sync->push($user);
    }
}
