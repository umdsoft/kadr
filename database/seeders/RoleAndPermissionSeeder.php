<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * TT бўлим 8 — Фойдаланувчи роллари ва ҳуқуқлари.
 *
 * 6 та рол:
 * 1. Super Admin — барча функциялар
 * 2. HR бошлиғи — барча ходимлар, маош, буйруқлар, ҳисоботлар
 * 3. HR ходими — ходим қўшиш/таҳрирлаш (маошдан ташқари)
 * 4. Бўлим бошлиғи — фақат ўз бўлими
 * 5. Ходим — фақат ўз кабинети
 * 6. Аудитор — фақат ўқиш + аудит журнал
 */
class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Кэшни тозалаш
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === PERMISSION lar ===

        // Ходим (employee) CRUD
        $employeePermissions = [
            'employee.view',
            'employee.view.own-department',
            'employee.view.own',
            'employee.create',
            'employee.update',
            'employee.update.own',
            'employee.delete',
            'employee.force-delete',
            'employee.restore',
            'employee.export',
            'employee.export.bulk',
        ];

        // Маош ва молиявий маълумотлар
        $salaryPermissions = [
            'salary.view',
            'salary.update',
        ];

        // Буйруқлар
        $orderPermissions = [
            'order.view',
            'order.create',
            'order.update',
            'order.delete',
        ];

        // Ҳисоботлар
        $reportPermissions = [
            'report.view',
            'report.demographic',
            'report.department',
            'report.full',
        ];

        // Тизим бошқаруви
        $systemPermissions = [
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'role.manage',
            'catalog.manage',
            'template.manage',
            'audit.view',
            'settings.manage',
        ];

        $allPermissions = array_merge(
            $employeePermissions,
            $salaryPermissions,
            $orderPermissions,
            $reportPermissions,
            $systemPermissions,
        );

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // === ROLLAR ===

        // 1. Super Admin — барча ҳуқуқлар
        $superAdmin = Role::create(['name' => 'super-admin']);
        // Spatie: super-admin avtomatik barcha permissionlarga ega
        // Gate::before da tekshiriladi

        // 2. HR бошлиғи
        $hrBoss = Role::create(['name' => 'hr-boss']);
        $hrBoss->givePermissionTo([
            'employee.view', 'employee.create', 'employee.update',
            'employee.delete', 'employee.restore',
            'employee.export', 'employee.export.bulk',
            'salary.view', 'salary.update',
            'order.view', 'order.create', 'order.update', 'order.delete',
            'report.view', 'report.demographic', 'report.department', 'report.full',
            'audit.view',
        ]);

        // 3. HR ходими
        $hrStaff = Role::create(['name' => 'hr-staff']);
        $hrStaff->givePermissionTo([
            'employee.view', 'employee.create', 'employee.update',
            'employee.export',
            'order.view', 'order.create',
            'report.view', 'report.demographic', 'report.department',
        ]);

        // 4. Бўлим бошлиғи — фақат ўз бўлими
        $deptHead = Role::create(['name' => 'department-head']);
        $deptHead->givePermissionTo([
            'employee.view.own-department',
            'employee.export',
            'report.view', 'report.department',
        ]);

        // 5. Ходим — фақат ўзини кўриш ва ўзгартириш сўрови
        $employee = Role::create(['name' => 'employee']);
        $employee->givePermissionTo([
            'employee.view.own',
            'employee.update.own',
        ]);

        // 6. Аудитор — фақат ўқиш + аудит
        $auditor = Role::create(['name' => 'auditor']);
        $auditor->givePermissionTo([
            'employee.view',
            'order.view',
            'report.view', 'report.demographic', 'report.department', 'report.full',
            'audit.view',
        ]);
    }
}
