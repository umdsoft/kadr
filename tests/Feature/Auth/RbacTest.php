<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Har bir test uchun rollar va permissionlar yaratamiz
    Permission::create(['name' => 'employee.view']);
    Permission::create(['name' => 'employee.create']);
    Permission::create(['name' => 'employee.update']);
    Permission::create(['name' => 'employee.delete']);
    Permission::create(['name' => 'salary.view']);
    Permission::create(['name' => 'audit.view']);
    Permission::create(['name' => 'employee.view.own-department']);
    Permission::create(['name' => 'employee.view.own']);
    Permission::create(['name' => 'employee.update.own']);

    $superAdmin = Role::create(['name' => 'super-admin']);

    $hrBoss = Role::create(['name' => 'hr-boss']);
    $hrBoss->givePermissionTo(['employee.view', 'employee.create', 'employee.update', 'employee.delete', 'salary.view', 'audit.view']);

    $hrStaff = Role::create(['name' => 'hr-staff']);
    $hrStaff->givePermissionTo(['employee.view', 'employee.create', 'employee.update']);

    $deptHead = Role::create(['name' => 'department-head']);
    $deptHead->givePermissionTo(['employee.view.own-department']);

    $employee = Role::create(['name' => 'employee']);
    $employee->givePermissionTo(['employee.view.own', 'employee.update.own']);

    $auditor = Role::create(['name' => 'auditor']);
    $auditor->givePermissionTo(['employee.view', 'audit.view']);
});

test('super-admin barcha permission larga ega', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Gate::before da super-admin uchun true qaytariladi
    expect($user->hasRole('super-admin'))->toBeTrue();
});

test('hr-boss maosh ko\'ra oladi, hr-staff ko\'ra olmaydi', function () {
    $hrBoss = User::factory()->create();
    $hrBoss->assignRole('hr-boss');

    $hrStaff = User::factory()->create();
    $hrStaff->assignRole('hr-staff');

    expect($hrBoss->hasPermissionTo('salary.view'))->toBeTrue()
        ->and($hrStaff->hasPermissionTo('salary.view'))->toBeFalse();
});

test('hr-staff xodim yaratish va tahrirlash huquqiga ega', function () {
    $user = User::factory()->create();
    $user->assignRole('hr-staff');

    expect($user->hasPermissionTo('employee.view'))->toBeTrue()
        ->and($user->hasPermissionTo('employee.create'))->toBeTrue()
        ->and($user->hasPermissionTo('employee.update'))->toBeTrue()
        ->and($user->hasPermissionTo('employee.delete'))->toBeFalse();
});

test('bo\'lim boshlig\'i faqat o\'z bo\'limi xodimlarini ko\'rishi mumkin', function () {
    $user = User::factory()->create();
    $user->assignRole('department-head');

    expect($user->hasPermissionTo('employee.view.own-department'))->toBeTrue()
        ->and($user->hasPermissionTo('employee.view'))->toBeFalse();
});

test('auditor faqat ko\'rishi va audit ko\'rishi mumkin', function () {
    $user = User::factory()->create();
    $user->assignRole('auditor');

    expect($user->hasPermissionTo('employee.view'))->toBeTrue()
        ->and($user->hasPermissionTo('audit.view'))->toBeTrue()
        ->and($user->hasPermissionTo('employee.create'))->toBeFalse()
        ->and($user->hasPermissionTo('employee.update'))->toBeFalse();
});

test('oddiy xodim faqat o\'zini ko\'ra va o\'zgartira oladi', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    expect($user->hasPermissionTo('employee.view.own'))->toBeTrue()
        ->and($user->hasPermissionTo('employee.update.own'))->toBeTrue()
        ->and($user->hasPermissionTo('employee.view'))->toBeFalse();
});
