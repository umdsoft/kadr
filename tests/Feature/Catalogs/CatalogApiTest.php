<?php

use App\Models\Department;
use App\Models\District;
use App\Models\Nationality;
use App\Models\Region;
use App\Models\User;

test('regions API faol viloyatlarni qaytaradi', function () {
    $user = User::factory()->create();
    Region::create(['name_cyr' => 'Хоразм вилояти', 'name_lat' => 'Xorazm viloyati', 'code' => '1733']);
    Region::create(['name_cyr' => 'Нофаол вилоят', 'name_lat' => 'Nofaol', 'code' => '9999', 'is_active' => false]);

    $response = $this->actingAs($user)->getJson('/api/catalogs/regions');

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name_cyr' => 'Хоразм вилояти']);
});

test('districts API viloyat bo\'yicha tumanlarni qaytaradi', function () {
    $user = User::factory()->create();
    $region = Region::create(['name_cyr' => 'Хоразм вилояти', 'name_lat' => 'Xorazm viloyati', 'code' => '1733']);

    District::create([
        'region_id' => $region->id,
        'name_cyr' => 'Урганч шаҳри',
        'name_lat' => 'Urganch shahri',
        'code' => '1733-01',
        'is_city' => true,
    ]);

    $response = $this->actingAs($user)->getJson("/api/catalogs/regions/{$region->id}/districts");

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name_cyr' => 'Урганч шаҳри', 'is_city' => true]);
});

test('nationalities API millatlar ro\'yxatini qaytaradi', function () {
    $user = User::factory()->create();
    Nationality::create(['name_cyr' => 'ўзбек', 'name_lat' => 'o\'zbek', 'sort_order' => 1]);
    Nationality::create(['name_cyr' => 'тожик', 'name_lat' => 'tojik', 'sort_order' => 2]);

    $response = $this->actingAs($user)->getJson('/api/catalogs/nationalities');

    $response->assertOk()
        ->assertJsonCount(2);
});

test('departments API ierarxik tuzilmani qaytaradi', function () {
    $user = User::factory()->create();
    $parent = Department::create([
        'name_cyr' => 'Ҳокимлик',
        'name_lat' => 'Hokimlik',
        'code' => 'HOK',
    ]);

    Department::create([
        'parent_id' => $parent->id,
        'name_cyr' => 'Кадрлар бўлими',
        'name_lat' => 'Kadrlar bo\'limi',
        'code' => 'KADR',
    ]);

    $response = $this->actingAs($user)->getJson('/api/catalogs/departments');

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name_cyr' => 'Ҳокимлик']);
});
