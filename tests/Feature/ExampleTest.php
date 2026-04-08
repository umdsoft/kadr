<?php

use App\Models\User;

test('bosh sahifa autentifikatsiya talab qiladi', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('login sahifasi yuklaydi', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

// Dashboard testlari DashboardTest.php ga ko'chirildi
