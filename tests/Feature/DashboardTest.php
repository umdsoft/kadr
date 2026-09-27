<?php

declare(strict_types=1);

use App\Models\Employee;
use App\Models\User;

test('dashboard tenant rejimida statistika bilan yuklaydi', function () {
    $user = User::factory()->create();

    // Biroz ma'lumot (tenant context null bo'lgani uchun global scope hammasini sanaydi)
    Employee::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('mode', 'tenant')
            ->where('stats.total_employees', 3)
            ->has('control_plan_stats')
            ->has('recent_activity'),
        );
});

test('dashboard autentifikatsiyasiz kirib bo\'lmaydi', function () {
    $this->get('/')->assertRedirect('/login');
});
