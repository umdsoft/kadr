<?php

use App\Models\Department;
use App\Models\District;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Region;
use App\Search\EmployeeSearchService;

beforeEach(function () {
    $this->region = Region::create(['name_cyr' => 'Хоразм вилояти', 'name_lat' => 'Xorazm viloyati', 'code' => '1733']);
    $this->district1 = District::create(['region_id' => $this->region->id, 'name_cyr' => 'Урганч шаҳри', 'name_lat' => 'Urganch', 'code' => '1733-01']);
    $this->district2 = District::create(['region_id' => $this->region->id, 'name_cyr' => 'Хива шаҳри', 'name_lat' => 'Xiva', 'code' => '1733-02']);
    $this->dept1 = Department::create(['name_cyr' => 'Кадрлар', 'name_lat' => 'Kadrlar', 'code' => 'KAD']);
    $this->dept2 = Department::create(['name_cyr' => 'Молия', 'name_lat' => 'Moliya', 'code' => 'MOL']);
    $this->position = Position::create(['name_cyr' => 'Мутахассис', 'name_lat' => 'Mutaxassis']);

    // 3 та тест ходим
    Employee::factory()->create([
        'last_name_cyr' => 'Эшматов', 'first_name_cyr' => 'Эшмат',
        'birth_date' => '1990-01-15', 'birth_district_id' => $this->district1->id,
        'department_id' => $this->dept1->id, 'education_level' => 'олий',
        'nationality' => 'ўзбек', 'specialty_by_education' => 'иқтисодчи',
    ]);
    Employee::factory()->create([
        'last_name_cyr' => 'Тошматов', 'first_name_cyr' => 'Тошмат',
        'birth_date' => '1985-06-20', 'birth_district_id' => $this->district2->id,
        'department_id' => $this->dept2->id, 'education_level' => 'ўрта махсус',
        'nationality' => 'тожик', 'specialty_by_education' => 'бухгалтер',
    ]);
    Employee::factory()->create([
        'last_name_cyr' => 'Каримова', 'first_name_cyr' => 'Гулнора',
        'birth_date' => '1995-12-01', 'birth_district_id' => $this->district1->id,
        'department_id' => $this->dept1->id, 'education_level' => 'олий',
        'nationality' => 'ўзбек', 'specialty_by_education' => 'ҳуқуқшунос',
    ]);

    $this->service = app(EmployeeSearchService::class);
});

test('ism bo\'yicha qidiruv ishlaydi', function () {
    $result = $this->service->search(['search' => 'Эшмат']);

    expect($result->total())->toBe(1)
        ->and($result->items()[0]->last_name_cyr)->toBe('Эшматов');
});

test('bo\'lim bo\'yicha filtrlash ishlaydi', function () {
    $result = $this->service->search(['department_id' => $this->dept1->id]);

    expect($result->total())->toBe(2);
});

test('ma\'lumot darajasi bo\'yicha filtrlash ishlaydi', function () {
    $result = $this->service->search(['education_level' => 'олий']);

    expect($result->total())->toBe(2);
});

test('millat bo\'yicha filtrlash ishlaydi', function () {
    $result = $this->service->search(['nationality' => 'тожик']);

    expect($result->total())->toBe(1)
        ->and($result->items()[0]->last_name_cyr)->toBe('Тошматов');
});

test('tuman bo\'yicha filtrlash ishlaydi', function () {
    $result = $this->service->search(['birth_district_id' => $this->district2->id]);

    expect($result->total())->toBe(1)
        ->and($result->items()[0]->last_name_cyr)->toBe('Тошматов');
});

test('tug\'ilgan sana oralig\'i bo\'yicha filtrlash ishlaydi', function () {
    $result = $this->service->search([
        'birth_date_range' => ['from' => '1988-01-01', 'to' => '1992-12-31'],
    ]);

    expect($result->total())->toBe(1)
        ->and($result->items()[0]->last_name_cyr)->toBe('Эшматов');
});

test('mutaxassislik bo\'yicha filtrlash ishlaydi', function () {
    $result = $this->service->search(['specialty' => 'ҳуқуқшунос']);

    expect($result->total())->toBe(1)
        ->and($result->items()[0]->last_name_cyr)->toBe('Каримова');
});

test('bir nechta filter birgalikda ishlaydi (AND)', function () {
    $result = $this->service->search([
        'department_id' => $this->dept1->id,
        'education_level' => 'олий',
        'nationality' => 'ўзбек',
    ]);

    expect($result->total())->toBe(2); // Эшматов ва Каримова
});

test('mos kelmaydigan filterlar bo\'sh natija qaytaradi', function () {
    $result = $this->service->search([
        'department_id' => $this->dept2->id,
        'nationality' => 'ўзбек',
    ]);

    expect($result->total())->toBe(0);
});

test('bo\'sh filter barcha xodimlarni qaytaradi', function () {
    $result = $this->service->search([]);

    expect($result->total())->toBe(3);
});
