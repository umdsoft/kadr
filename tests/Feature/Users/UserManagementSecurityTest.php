<?php

declare(strict_types=1);

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

/**
 * C1 — UserController avtorizatsiyasi (privilegiya eskalatsiyasi, tenant izolyatsiyasi,
 * amal-darajasidagi ruxsat). Audit topgan tizim egallash teshigini yopadi.
 */
beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(DepartmentSeeder::class);
    $this->seed(PositionSeeder::class);

    $this->rootA = Department::whereNull('parent_id')->orderBy('id')->first();
    $this->kompleksA = Department::where('type', 'kompleks')->where('parent_id', $this->rootA->id)->first();

    // Ikkinchi hokimlik (boshqa tenant)
    $this->rootB = Department::create([
        'name_cyr' => 'Иккинчи ҳокимлик', 'name_lat' => 'Ikkinchi hokimlik', 'code' => 'HOK-B', 'type' => 'hokimlik',
    ]);

    // A hokimligi tuman-admini (user.view/create/update bor, user.delete YO'Q)
    $this->tumanAdminA = User::create([
        'name' => 'Туман админ A', 'login' => 'tuman_a',
        'password' => bcrypt('parol1234'), 'department_id' => $this->rootA->id,
    ]);
    $this->tumanAdminA->assignRole('tuman-admin');

    $this->superAdminPosition = Position::where('role_name', 'super-admin')->firstOrFail();
    $this->mutaxassisPosition = Position::where('role_name', 'mutaxassis')->firstOrFail();
});

it('tuman-admin poyga super-admin rolini tayinlay OLMAYDI (privilegiya eskalatsiyasi)', function () {
    $response = $this->actingAs($this->tumanAdminA)->post('/users', [
        'name' => 'Ёмон ният',
        'login' => 'evil_admin',
        'password' => 'parol12345',
        'department_id' => $this->rootA->id,
        'position_id' => $this->superAdminPosition->id, // super-admin lavozimi orqali eskalatsiya urinishi
    ]);

    $response->assertForbidden();
    expect(User::where('login', 'evil_admin')->exists())->toBeFalse();
});

it('tuman-admin boshqa hokimlik foydalanuvchisini tahrirlay OLMAYDI (tenant izolyatsiyasi)', function () {
    $targetB = User::create([
        'name' => 'B ходими', 'login' => 'user_b',
        'password' => bcrypt('parol1234'), 'department_id' => $this->rootB->id,
    ]);
    $targetB->assignRole('mutaxassis');

    $this->actingAs($this->tumanAdminA)->put("/users/{$targetB->id}", [
        'name' => 'Ўзгартирилди',
        'login' => 'user_b',
        'department_id' => $this->rootB->id,
        'position_id' => $this->mutaxassisPosition->id,
    ])->assertForbidden();
});

it('tuman-admin user.delete ruxsatisiz foydalanuvchini o\'chira OLMAYDI', function () {
    $targetA = User::create([
        'name' => 'A ходими', 'login' => 'user_a',
        'password' => bcrypt('parol1234'), 'department_id' => $this->kompleksA->id,
    ]);
    $targetA->assignRole('mutaxassis');

    $this->actingAs($this->tumanAdminA)->delete("/users/{$targetA->id}")
        ->assertForbidden();

    expect(User::find($targetA->id))->not->toBeNull();
});

it('tuman-admin o\'z tenantida oddiy foydalanuvchi yarata OLADI', function () {
    $this->actingAs($this->tumanAdminA)->post('/users', [
        'name' => 'Янги мутахассис',
        'login' => 'yangi_mutaxassis',
        'password' => 'parol12345',
        'department_id' => $this->kompleksA->id,
        'position_id' => $this->mutaxassisPosition->id,
    ])->assertRedirect(route('users.index'));

    $created = User::where('login', 'yangi_mutaxassis')->first();
    expect($created)->not->toBeNull()
        ->and($created->hasRole('mutaxassis'))->toBeTrue()
        ->and($created->hasRole('super-admin'))->toBeFalse();
});

it('ruxsatsiz foydalanuvchi (mutaxassis) users bo\'limiga kira OLMAYDI', function () {
    $plain = User::create([
        'name' => 'Оддий', 'login' => 'oddiy1',
        'password' => bcrypt('parol1234'), 'department_id' => $this->kompleksA->id,
    ]);
    $plain->assignRole('mutaxassis');

    $this->actingAs($plain)->get('/users')->assertForbidden();
});
