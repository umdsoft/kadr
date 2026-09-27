<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('audit sahifasi faqat audit.view permission bilan ochiladi', function () {
    Permission::create(['name' => 'audit.view']);
    $role = Role::create(['name' => 'auditor']);
    $role->givePermissionTo('audit.view');

    $user = User::factory()->create();
    $user->assignRole('auditor');

    $response = $this->actingAs($user)->get('/audit');

    $response->assertStatus(200);
});

test('audit sahifasi permissionsiz 403 qaytaradi', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/audit');

    $response->assertStatus(403);
});
