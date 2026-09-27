<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CitizenAppeal;
use App\Models\CouncilMember;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAccess;

class CitizenAppealPolicy
{
    use ChecksTenantAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('appeals.view');
    }

    public function view(User $user, CitizenAppeal $appeal): bool
    {
        if (! $user->hasPermissionTo('appeals.view') || ! $this->sameTenant($user, $appeal)) {
            return false;
        }

        // Tenant ichida — hamma murojaatni ko'rmaydi
        if ($user->hasPermissionTo('appeals.view-all') || $this->isCrossTenantAdmin($user)) {
            return true;
        }

        // Mahalla yettiligi a'zosi — faqat o'z mahallasi
        if ($user->hasRole('mahalla-yettiligi')) {
            // user.department_id = mahalla bog'liq emas — boshqacha tekshiruv
            // Hozircha: agar appeal mahalla'ga assigned bo'lsa va user shu council'da bo'lsa
            return $this->isCouncilMember($user, $appeal);
        }

        return true; // tenant ichida boshqa rollar — ko'radi
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('appeals.create');
    }

    public function update(User $user, CitizenAppeal $appeal): bool
    {
        return $user->hasPermissionTo('appeals.update') && $this->sameTenant($user, $appeal);
    }

    public function delete(User $user, CitizenAppeal $appeal): bool
    {
        return $user->hasPermissionTo('appeals.delete') && $this->sameTenant($user, $appeal);
    }

    public function assign(User $user, CitizenAppeal $appeal): bool
    {
        return $user->hasPermissionTo('appeals.assign') && $this->sameTenant($user, $appeal);
    }

    public function decide(User $user, CitizenAppeal $appeal): bool
    {
        if (! $user->hasPermissionTo('appeals.decide') || ! $this->sameTenant($user, $appeal)) {
            return false;
        }

        // Faqat appeal'ga biriktirilgan yettilik a'zolari qaror chiqaradi
        if ($this->isCrossTenantAdmin($user) || $user->hasRole('tuman-admin')) {
            return true;
        }

        return $this->isCouncilMember($user, $appeal);
    }

    private function isCouncilMember(User $user, CitizenAppeal $appeal): bool
    {
        // Active assignment'ga biriktirilgan council'ning a'zosi
        $assignment = $appeal->activeAssignment;
        if (! $assignment || $assignment->assignee_type !== 'council') {
            return false;
        }

        return CouncilMember::query()
            ->where('council_id', $assignment->assignee_id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }
}
