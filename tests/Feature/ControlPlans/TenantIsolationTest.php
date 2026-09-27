<?php

declare(strict_types=1);

use App\Models\ControlPlanItem;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

/**
 * HIGH-2 — cross-tenant IDOR: A hokimligi admini B hokimligi topshirig'ini
 * ko'ra OLMASLIGI kerak (route-binding tenant scope + canViewItem sameTenant).
 */
beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(DepartmentSeeder::class);

    $this->rootA = Department::whereNull('parent_id')->orderBy('id')->first();
    $this->rootB = Department::create([
        'name_cyr' => 'B ҳокимлик', 'name_lat' => 'B hokimlik', 'code' => 'HOK-B2', 'type' => 'hokimlik',
    ]);

    $this->adminA = User::create([
        'name' => 'Туман админ A', 'login' => 'tuman_a2',
        'password' => bcrypt('parol1234'), 'department_id' => $this->rootA->id,
    ]);
    $this->adminA->assignRole('tuman-admin');

    // B hokimligidagi topshiriq (mustaqil band)
    $this->itemB = ControlPlanItem::create([
        'source' => 'standalone',
        'hokimlik_id' => $this->rootB->id,
        'task_description' => 'B hokimligi maxfiy topshirig\'i',
        'execution_status' => 'not_started',
    ]);
});

it('A admini B topshirig\'ini ko\'ra OLMAYDI (route detali)', function () {
    $this->actingAs($this->adminA)
        ->get("/topshiriqlar/{$this->itemB->id}")
        ->assertStatus(404); // tenant scope route-binding'da → 404 (yoki 403)
});

it('A admini nazorat reja bandi (B) detalini ko\'ra OLMAYDI', function () {
    // Tenant scope findOrFail'da band topilmaydi → 404 (himoya).
    $this->actingAs($this->adminA)
        ->get("/control-plans/items/{$this->itemB->id}")
        ->assertNotFound();
});

it('super-admin B topshirig\'ini ko\'ra OLADI (cross-tenant)', function () {
    $super = User::create([
        'name' => 'Super', 'login' => 'super2',
        'password' => bcrypt('parol1234'), 'department_id' => $this->rootA->id,
    ]);
    $super->assignRole('super-admin');

    $this->actingAs($super)
        ->get("/topshiriqlar/{$this->itemB->id}")
        ->assertOk();
});
