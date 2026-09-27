<?php

declare(strict_types=1);

use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\QueryException;

/**
 * 2-bosqich — sxema butunligi: idempotent seederlar (M6) va
 * departments.parent_id restrictOnDelete (H6).
 */
it('RoleAndPermissionSeeder ikki marta ishga tushsa xato bermaydi (idempotent)', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $countAfterFirst = Role::count();

    $this->seed(RoleAndPermissionSeeder::class); // qayta

    expect(Role::count())->toBe($countAfterFirst)
        ->and($countAfterFirst)->toBeGreaterThan(0);
});

it('Department/Position seederlar idempotent — dublikat yaratmaydi', function () {
    $this->seed(DepartmentSeeder::class);
    $this->seed(PositionSeeder::class);
    $depts = Department::count();
    $positions = Position::count();

    $this->seed(DepartmentSeeder::class);
    $this->seed(PositionSeeder::class);

    expect(Department::count())->toBe($depts)
        ->and(Position::count())->toBe($positions);
});

it('bolasi bor bo\'limni o\'chirib bo\'lmaydi (restrictOnDelete — tenant izolyatsiyasi)', function () {
    $parent = Department::create(['name_cyr' => 'Ота', 'name_lat' => 'Ota', 'code' => 'OTA', 'type' => 'hokimlik']);
    Department::create(['name_cyr' => 'Бола', 'name_lat' => 'Bola', 'code' => 'BOLA', 'parent_id' => $parent->id]);

    expect(fn () => $parent->delete())->toThrow(QueryException::class);
});
