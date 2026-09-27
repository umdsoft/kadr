<?php

declare(strict_types=1);

use App\Models\Department;
use App\Models\District;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    // Super-admin — Gate::before orqali barcha policy va tenant tekshiruvlaridan o'tadi.
    // (Bu yerda CRUD funksiyasi sinaladi; ruxsat rad etilishi RbacTest da sinaladi.)
    Role::create(['name' => 'super-admin']);

    // Katalog ma'lumotlari
    $this->region = Region::create(['name_cyr' => 'Хоразм вилояти', 'name_lat' => 'Xorazm viloyati', 'code' => '1733']);
    $this->district = District::create([
        'region_id' => $this->region->id,
        'name_cyr' => 'Урганч шаҳри',
        'name_lat' => 'Urganch shahri',
        'code' => '1733-01',
    ]);
    $this->department = Department::create(['name_cyr' => 'Кадрлар бўлими', 'name_lat' => 'Kadrlar', 'code' => 'KAD']);
    $this->position = Position::create(['name_cyr' => 'Мутахассис', 'name_lat' => 'Mutaxassis']);

    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
});

function validEmployeeData(object $test): array
{
    return [
        'last_name_cyr' => 'Эшматов',
        'first_name_cyr' => 'Эшмат',
        'middle_name_cyr' => 'Тошматович',
        'current_position' => 'Хоразм вилояти ҳокимлиги кадрлар бўлими бош мутахассиси',
        'position_start_date' => '2024-01-15',
        'birth_date' => '1990-05-20',
        'birth_place' => 'Хоразм вилояти, Урганч шаҳри',
        'birth_region_id' => $test->region->id,
        'birth_district_id' => $test->district->id,
        'nationality' => 'ўзбек',
        'party_affiliation' => 'йўқ',
        'education_level' => 'олий',
        'education_completion' => '2012 йил, Урганч давлат университети (кундузги)',
        'specialty_by_education' => 'иқтисодчи',
        'academic_degree' => 'йўқ',
        'academic_title' => 'йўқ',
        'foreign_languages' => 'инглиз тили',
        'state_awards' => 'тақдирланмаган',
        'elected_body_member' => 'йўқ',
        'jshshir' => '12345678901234',
        'passport_series' => 'AB',
        'passport_number' => '1234567',
        'department_id' => $test->department->id,
        'position_id' => $test->position->id,
    ];
}

test('xodimlar ro\'yxati sahifasi yuklaydi', function () {
    $response = $this->actingAs($this->user)->get(route('employees.index'));

    $response->assertStatus(200);
});

test('yangi xodim yaratish sahifasi yuklaydi', function () {
    $response = $this->actingAs($this->user)->get(route('employees.create'));

    $response->assertStatus(200);
});

test('yangi xodim muvaffaqiyatli yaratiladi', function () {
    $data = validEmployeeData($this);

    $response = $this->actingAs($this->user)->post(route('employees.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('employees', [
        'last_name_cyr' => 'Эшматов',
        'first_name_cyr' => 'Эшмат',
    ]);
});

test('xodim ma\'lumotlari ko\'riladi', function () {
    $employee = Employee::factory()->create();

    $response = $this->actingAs($this->user)->get(route('employees.show', $employee->id));

    $response->assertStatus(200);
});

test('xodim ma\'lumotlari yangilanadi', function () {
    $employee = Employee::factory()->create();
    $data = validEmployeeData($this);
    $data['last_name_cyr'] = 'Янгиланганов';
    $data['jshshir'] = '99999999999999';

    $response = $this->actingAs($this->user)->put(route('employees.update', $employee->id), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'last_name_cyr' => 'Янгиланганов',
    ]);
});

test('xodim soft delete qilinadi', function () {
    $employee = Employee::factory()->create();

    $response = $this->actingAs($this->user)->delete(route('employees.destroy', $employee->id));

    $response->assertRedirect(route('employees.index'));
    $this->assertSoftDeleted('employees', ['id' => $employee->id]);
});

test('qisqartirmali ism bilan xodim yaratib bo\'lmaydi', function () {
    $data = validEmployeeData($this);
    $data['birth_place'] = 'Тош. вил., Қибрай тум.';

    $response = $this->actingAs($this->user)->post(route('employees.store'), $data);

    $response->assertSessionHasErrors('birth_place');
});

test('16 yoshdan kichik xodim yaratib bo\'lmaydi', function () {
    $data = validEmployeeData($this);
    $data['birth_date'] = now()->subYears(15)->format('Y-m-d');

    $response = $this->actingAs($this->user)->post(route('employees.store'), $data);

    $response->assertSessionHasErrors('birth_date');
});
