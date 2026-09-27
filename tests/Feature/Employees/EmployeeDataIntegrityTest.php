<?php

declare(strict_types=1);

use App\Actions\WorkHistory\SaveWorkHistoryAction;
use App\Models\District;
use App\Models\Employee;
use App\Models\Region;
use App\Models\Relative;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkHistory;

/**
 * 2-bosqich — ma'lumotlar butunligi: tranzaksiya rollback, jshshir soft-delete
 * ziddiyati, region/tuman mosligi.
 */
beforeEach(function () {
    Role::create(['name' => 'super-admin']);
    $this->region = Region::create(['name_cyr' => 'Хоразм', 'name_lat' => 'Xorazm', 'code' => '1733']);
    $this->district = District::create([
        'region_id' => $this->region->id, 'name_cyr' => 'Урганч', 'name_lat' => 'Urganch', 'code' => '1733-01',
    ]);
    $this->otherRegion = Region::create(['name_cyr' => 'Тошкент', 'name_lat' => 'Toshkent', 'code' => '1726']);

    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
});

it('SaveWorkHistoryAction xatoda mavjud yozuvlarni yo\'qotmaydi (tranzaksiya rollback)', function () {
    $employee = Employee::factory()->create();
    // Mavjud ish tarixi yozuvi
    $employee->workHistory()->create([
        'start_year' => 2000, 'organization_full' => 'Эски ташкилот',
        'position_full' => 'Эски лавозим', 'sort_order' => 0,
    ]);
    expect($employee->workHistory()->count())->toBe(1);

    // Yaroqsiz yangi item (organization_full — NOT NULL) — create() o'rtada 500 beradi
    $badItems = [[
        'start_year' => 2010,
        'organization_full' => null, // NOT NULL buziladi
        'position_full' => 'Янги',
    ]];

    try {
        app(SaveWorkHistoryAction::class)->execute($employee->fresh(), $badItems);
    } catch (Throwable $e) {
        // kutilgan xato
    }

    // Rollback tufayli eski yozuv joyida qolishi kerak (delete qaytariladi)
    expect($employee->workHistory()->count())->toBe(1)
        ->and($employee->workHistory()->first()->organization_full)->toBe('Эски ташкилот');
});

it('o\'chirilgan (soft-deleted) xodim JSHSHIR bilan qayta yaratishda 500 emas, tushunarli xato beradi', function () {
    $data = validIntegrityEmployeeData($this);
    // Birinchi xodim
    $this->actingAs($this->user)->post('/employees', $data)->assertRedirect();

    $employee = Employee::where('jshshir_hash', Employee::hashJshshir($data['jshshir']))->first();
    $employee->delete(); // soft delete

    // Xuddi shu JSHSHIR bilan qayta — 500 emas, validatsiya xatosi
    $this->actingAs($this->user)->post('/employees', $data)
        ->assertSessionHasErrors('jshshir');
});

it('boshqa viloyatga tegishli tuman rad etiladi', function () {
    $data = validIntegrityEmployeeData($this);
    $data['birth_region_id'] = $this->otherRegion->id; // tuman Xorazm'niki, region Toshkent

    $this->actingAs($this->user)->post('/employees', $data)
        ->assertSessionHasErrors('birth_district_id');
});

it('xodim + ish tarixi + qarindosh birga yaratiladi (transaksiya muvaffaqiyatli)', function () {
    $data = validIntegrityEmployeeData($this);
    $data['work_history'] = [[
        'start_year' => 2015, 'organization_full' => 'Хоразм вилояти ҳокимлиги',
        'position_full' => 'Бош мутахассис',
    ]];
    $data['relatives'] = [[
        'relationship_type' => 'Отаси', 'full_name_cyr' => 'Эшматов Тошмат Полвонович',
        'birth_year' => 1960, 'birth_place' => 'Хоразм вилояти Урганч тумани',
        'is_deceased' => false, 'workplace_and_position' => 'Нафақада',
        'residence_full' => 'Хоразм вилояти Урганч тумани',
    ]];

    $this->actingAs($this->user)->post('/employees', $data)->assertRedirect();

    $employee = Employee::where('jshshir_hash', Employee::hashJshshir($data['jshshir']))->first();
    expect($employee)->not->toBeNull()
        ->and(WorkHistory::where('employee_id', $employee->id)->count())->toBe(1)
        ->and(Relative::where('employee_id', $employee->id)->count())->toBe(1);
});

it('vafot etgan qarindosh uchun former_position saqlanadi (M5)', function () {
    $employee = Employee::factory()->create();

    $this->actingAs($this->user)->put("/employees/{$employee->id}/relatives", [
        'relatives' => [[
            'relationship_type' => 'Отаси',
            'full_name_cyr' => 'Эшматов Тошмат Полвонович',
            'birth_year' => 1950,
            'birth_place' => 'Хоразм вилояти Урганч тумани',
            'is_deceased' => true,
            'deceased_year' => 2010,
            'former_position' => 'мактаб директори',
            'residence_full' => 'Хоразм вилояти Урганч тумани',
        ]],
    ])->assertRedirect();

    $rel = Relative::where('employee_id', $employee->id)->first();
    expect($rel)->not->toBeNull()
        ->and($rel->former_position)->toBe('мактаб директори');
});

function validIntegrityEmployeeData(object $test): array
{
    return [
        'last_name_cyr' => 'Эшматов', 'first_name_cyr' => 'Эшмат', 'middle_name_cyr' => 'Тошматович',
        'current_position' => 'Хоразм вилояти ҳокимлиги кадрлар бўлими бош мутахассиси',
        'position_start_date' => '2024-01-15', 'birth_date' => '1990-05-20',
        'birth_place' => 'Хоразм вилояти, Урганч шаҳри',
        'birth_region_id' => $test->region->id, 'birth_district_id' => $test->district->id,
        'nationality' => 'ўзбек', 'party_affiliation' => 'йўқ', 'education_level' => 'олий',
        'education_completion' => '2012 йил, Урганч давлат университети (кундузги)',
        'specialty_by_education' => 'иқтисодчи', 'academic_degree' => 'йўқ', 'academic_title' => 'йўқ',
        'foreign_languages' => 'инглиз тили', 'state_awards' => 'тақдирланмаган', 'elected_body_member' => 'йўқ',
        'jshshir' => '12345678901234', 'passport_series' => 'AB', 'passport_number' => '1234567',
    ];
}
