<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

/**
 * TT бўлим 8 — Ходим маълумотлари устидаги ҳуқуқлар.
 */
class EmployeePolicy
{
    /**
     * Рўйхатни кўриш.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['employee.view', 'employee.view.own-department']);
    }

    /**
     * Битта ходимни кўриш.
     */
    public function view(User $user, Employee $employee): bool
    {
        if ($user->hasPermissionTo('employee.view')) {
            return true;
        }

        // Бўлим бошлиғи — фақат ўз бўлими
        if ($user->hasPermissionTo('employee.view.own-department')) {
            return $user->department_id === $employee->department_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('employee.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('employee.update');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('employee.delete');
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('employee.restore');
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('employee.force-delete');
    }

    /**
     * DOCX экспорт қилиш.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo('employee.export');
    }
}
