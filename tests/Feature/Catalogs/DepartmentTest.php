<?php

use App\Models\Department;
use App\Models\Position;

test('bo\'lim yaratilishi va ierarxiya ishlashi mumkin', function () {
    $parent = Department::create([
        'name_cyr' => 'Хоразм вилояти ҳокимлиги',
        'name_lat' => 'Xorazm viloyati hokimligi',
        'code' => 'XVHOK',
    ]);

    $child = Department::create([
        'parent_id' => $parent->id,
        'name_cyr' => 'Кадрлар бўлими',
        'name_lat' => 'Kadrlar bo\'limi',
        'code' => 'KADR',
    ]);

    expect($parent->children)->toHaveCount(1)
        ->and($child->parent->id)->toBe($parent->id);
});

test('bo\'limga lavozim biriktirish mumkin', function () {
    $dept = Department::create([
        'name_cyr' => 'Молия бўлими',
        'name_lat' => 'Moliya bo\'limi',
        'code' => 'MOL',
    ]);

    Position::create([
        'department_id' => $dept->id,
        'name_cyr' => 'Бош бухгалтер',
        'name_lat' => 'Bosh buxgalter',
    ]);

    expect($dept->positions)->toHaveCount(1)
        ->and($dept->positions->first()->name_cyr)->toBe('Бош бухгалтер');
});

test('lavozim bo\'limsiz ham yaratilishi mumkin', function () {
    $position = Position::create([
        'name_cyr' => 'Маслаҳатчи',
        'name_lat' => 'Maslahatchi',
    ]);

    expect($position->department_id)->toBeNull()
        ->and($position->department)->toBeNull();
});
