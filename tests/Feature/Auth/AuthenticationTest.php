<?php

use App\Models\User;

test('login sahifasi render bo\'ladi', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('foydalanuvchi to\'g\'ri ma\'lumotlar bilan kiroladi', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/');
});

test('noto\'g\'ri parol bilan kirib bo\'lmaydi', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('foydalanuvchi logout qiloladi', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
});

test('autentifikatsiyasiz API ga kirib bo\'lmaydi', function () {
    $response = $this->getJson('/api/catalogs/regions');

    $response->assertUnauthorized();
});
