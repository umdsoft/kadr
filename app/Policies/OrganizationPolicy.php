<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAccess;

/**
 * Tashkilot ruxsatlari.
 *
 * Kotibyat mudiri faqat O'Z kompleksi yoki o'zi yaratgan tashkilotlarni boshqaradi.
 * Tuman/viloyat-admin va super-admin — tenant ichida keng huquq.
 */
class OrganizationPolicy
{
    use ChecksTenantAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tashkilotlar.view');
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->hasPermissionTo('tashkilotlar.view')
            && $this->sameTenant($user, $organization);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tashkilotlar.create');
    }

    public function update(User $user, Organization $organization): bool
    {
        if (! $user->hasPermissionTo('tashkilotlar.update') || ! $this->sameTenant($user, $organization)) {
            return false;
        }

        return $this->ownsOrManages($user, $organization);
    }

    public function delete(User $user, Organization $organization): bool
    {
        if (! $user->hasPermissionTo('tashkilotlar.delete') || ! $this->sameTenant($user, $organization)) {
            return false;
        }

        return $this->ownsOrManages($user, $organization);
    }

    /** Tashkilot uchun foydalanuvchi (admin/jamoa) yaratish/boshqarish. */
    public function manageUsers(User $user, Organization $organization): bool
    {
        if (! $user->hasPermissionTo('tashkilot.manage-users') || ! $this->sameTenant($user, $organization)) {
            return false;
        }

        return $this->ownsOrManages($user, $organization);
    }

    /**
     * Foydalanuvchi tashkilot egasimi (yaratgan), bir kompleksdami yoki tenant-admin'mi.
     */
    private function ownsOrManages(User $user, Organization $organization): bool
    {
        if ($this->isCrossTenantAdmin($user) || $user->hasRole('tuman-admin')) {
            return true;
        }

        // O'zi yaratgan
        if ($organization->created_by === $user->id) {
            return true;
        }

        // Bir kompleks (kotibyat mudirining department_id = kompleks)
        return $organization->kompleks_id !== null
            && $organization->kompleks_id === $user->department_id;
    }
}
