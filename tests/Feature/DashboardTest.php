<?php

use App\Models\Employee;
use App\Models\User;

test('dashboard statistika bilan yuklaydi', function () {
    $user = User::factory()->create();

    // Biroz ma'lumot yaratamiz
    Employee::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->where('stats.total_employees', 3)
            ->has('by_department')
            ->has('by_education')
            ->has('recent_activity')
        );
});

test('dashboard autentifikatsiyasiz kirib bo\'lmaydi', function () {
    $this->get('/')->assertRedirect('/login');
});
