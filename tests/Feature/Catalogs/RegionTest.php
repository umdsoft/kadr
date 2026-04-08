<?php

use App\Models\District;
use App\Models\Region;

test('viloyat yaratilishi mumkin', function () {
    $region = Region::create([
        'name_cyr' => 'Хоразм вилояти',
        'name_lat' => 'Xorazm viloyati',
        'code' => '1733',
    ]);

    $region->refresh();

    expect($region)->toBeInstanceOf(Region::class)
        ->and($region->name_cyr)->toBe('Хоразм вилояти')
        ->and($region->is_active)->toBeTrue();
});

test('viloyat tumanlarini olish mumkin', function () {
    $region = Region::create([
        'name_cyr' => 'Хоразм вилояти',
        'name_lat' => 'Xorazm viloyati',
        'code' => '1733',
    ]);

    District::create([
        'region_id' => $region->id,
        'name_cyr' => 'Урганч шаҳри',
        'name_lat' => 'Urganch shahri',
        'code' => '1733-01',
        'is_city' => true,
    ]);

    District::create([
        'region_id' => $region->id,
        'name_cyr' => 'Хива шаҳри',
        'name_lat' => 'Xiva shahri',
        'code' => '1733-02',
        'is_city' => true,
    ]);

    expect($region->districts)->toHaveCount(2)
        ->and($region->districts->first()->is_city)->toBeTrue();
});

test('viloyat kodi unique bo\'lishi shart', function () {
    Region::create([
        'name_cyr' => 'Хоразм вилояти',
        'name_lat' => 'Xorazm viloyati',
        'code' => '1733',
    ]);

    Region::create([
        'name_cyr' => 'Бошқа вилоят',
        'name_lat' => 'Boshqa viloyat',
        'code' => '1733',
    ]);
})->throws(\Illuminate\Database\QueryException::class);
