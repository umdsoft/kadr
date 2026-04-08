<?php

use App\Exports\EmployeeFormatter;
use App\Models\Employee;
use App\Models\Relative;
use App\Models\WorkHistory;

test('formatter to\'liq ism qaytaradi', function () {
    $employee = Employee::factory()->create([
        'last_name_cyr' => 'Эшматов',
        'first_name_cyr' => 'Эшмат',
        'middle_name_cyr' => 'Тошматович',
    ]);

    $formatter = new EmployeeFormatter;
    $data = $formatter->format($employee);

    expect($data['full_name'])->toBe('Эшматов Эшмат Тошматович');
});

test('formatter lavozim sanasini to\'g\'ri formatlaydi', function () {
    $employee = Employee::factory()->create([
        'position_start_date' => '2007-10-25',
    ]);

    $formatter = new EmployeeFormatter;
    $data = $formatter->format($employee);

    expect($data['position_start_date'])->toBe('2007 йил 25 октябрдан');
});

test('formatter mehnat faoliyatini formatlaydi', function () {
    $employee = Employee::factory()->create();

    WorkHistory::create([
        'employee_id' => $employee->id,
        'start_year' => 1977,
        'end_year' => 1982,
        'organization_full' => 'Тошкент давлат иқтисодиёт университети',
        'position_full' => 'талаба',
        'sort_order' => 0,
    ]);

    WorkHistory::create([
        'employee_id' => $employee->id,
        'start_year' => 1982,
        'end_year' => null,
        'organization_full' => 'Хоразм вилояти ҳокимлиги',
        'position_full' => 'бош мутахассис',
        'sort_order' => 1,
    ]);

    $formatter = new EmployeeFormatter;
    $data = $formatter->format($employee);

    expect($data['work_history'])->toHaveCount(2)
        ->and($data['work_history'][0]['years'])->toBe('1977-1982 йй.')
        ->and($data['work_history'][1]['years'])->toBe('1982-ҳ.в. йй.');
});

test('formatter qarinoshlarni formatlaydi', function () {
    $employee = Employee::factory()->create();

    Relative::create([
        'employee_id' => $employee->id,
        'relationship_type' => 'Отаси',
        'full_name_cyr' => 'Эшматов Тошмат Ҳайдарович',
        'birth_year' => 1950,
        'birth_place' => 'Хоразм вилояти, Урганч тумани',
        'is_deceased' => false,
        'workplace_and_position' => 'Пенсияда (Урганч тумани ҳокимлиги бўлим бошлиғи)',
        'residence_full' => 'Хоразм вилояти, Урганч шаҳри',
    ]);

    $formatter = new EmployeeFormatter;
    $data = $formatter->format($employee);

    expect($data['relatives'])->toHaveCount(1)
        ->and($data['relatives'][0]['relationship'])->toBe('Отаси')
        ->and($data['relatives'][0]['birth_year_place'])->toBe('1950 йил, Хоразм вилояти, Урганч тумани');
});
