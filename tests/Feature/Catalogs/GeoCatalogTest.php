<?php

use App\Models\District;
use App\Models\Mahalla;
use App\Models\Region;

test('geo ierarxiya: viloyat → tuman → mahalla', function () {
    $region = Region::create([
        'name_cyr' => 'Хоразм вилояти',
        'name_lat' => 'Xorazm viloyati',
        'code' => '1733',
    ]);

    $district = District::create([
        'region_id' => $region->id,
        'name_cyr' => 'Урганч шаҳри',
        'name_lat' => 'Urganch shahri',
        'code' => '1733-01',
        'is_city' => true,
    ]);

    $mahalla = Mahalla::create([
        'district_id' => $district->id,
        'name_cyr' => 'Янгибозор МФЙ',
        'name_lat' => 'Yangibozor MFY',
    ]);

    // Тўлиқ занжир текширув
    expect($mahalla->district->id)->toBe($district->id)
        ->and($mahalla->district->region->id)->toBe($region->id)
        ->and($district->mahallas)->toHaveCount(1);
});

test('tuman o\'chirilganda mahallalar himoyalangan (restrict)', function () {
    $region = Region::create([
        'name_cyr' => 'Хоразм вилояти',
        'name_lat' => 'Xorazm viloyati',
        'code' => '1733',
    ]);

    $district = District::create([
        'region_id' => $region->id,
        'name_cyr' => 'Урганч тумани',
        'name_lat' => 'Urganch tumani',
        'code' => '1733-06',
    ]);

    Mahalla::create([
        'district_id' => $district->id,
        'name_cyr' => 'Тест МФЙ',
        'name_lat' => 'Test MFY',
    ]);

    // FK RESTRICT — o'chirishga ruxsat yo'q
    $district->delete();
})->throws(\Illuminate\Database\QueryException::class);
