<?php

use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::create(['name' => 'employee.view']);
    Permission::create(['name' => 'employee.create']);
    Permission::create(['name' => 'employee.update']);

    $role = Role::create(['name' => 'hr-staff']);
    $role->givePermissionTo(['employee.view', 'employee.create', 'employee.update']);

    $this->user = User::factory()->create();
    $this->user->assignRole('hr-staff');
    $this->employee = Employee::factory()->create();
});

test('qarinoshlar muvaffaqiyatli saqlanadi', function () {
    $data = [
        'relatives' => [
            [
                'relationship_type' => 'Отаси',
                'full_name_cyr' => 'Тестов Тест Тестович',
                'birth_year' => 1960,
                'birth_place' => 'Хоразм вилояти, Урганч шаҳри',
                'is_deceased' => false,
                'workplace_and_position' => 'Пенсияда (Урганч шаҳар 5-мактаб директори)',
                'residence_full' => 'Хоразм вилояти, Урганч шаҳри, Янгибозор кўчаси 15-уй',
            ],
            [
                'relationship_type' => 'Онаси',
                'full_name_cyr' => 'Тестова Тест Тестовна',
                'birth_year' => 1965,
                'birth_place' => 'Хоразм вилояти, Хива шаҳри',
                'is_deceased' => false,
                'workplace_and_position' => 'Урганч шаҳар 10-мактаб ўқитувчиси',
                'residence_full' => 'Хоразм вилояти, Урганч шаҳри, Янгибозор кўчаси 15-уй',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('employees.relatives.save', $this->employee->id), $data);

    $response->assertRedirect();
    $this->assertDatabaseCount('employee_relatives', 2);
    $this->assertDatabaseHas('employee_relatives', [
        'employee_id' => $this->employee->id,
        'relationship_type' => 'Отаси',
    ]);
});

test('vafot etgan qarinosh auto-formatlanadi', function () {
    $data = [
        'relatives' => [
            [
                'relationship_type' => 'Отаси',
                'full_name_cyr' => 'Тестов Тест Тестович',
                'birth_year' => 1945,
                'birth_place' => 'Хоразм вилояти, Урганч тумани',
                'is_deceased' => true,
                'deceased_year' => 1992,
                'former_position' => '1-марказий поликлиника шифокори',
                'residence_full' => 'Хоразм вилояти, Урганч шаҳри',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('employees.relatives.save', $this->employee->id), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('employee_relatives', [
        'employee_id' => $this->employee->id,
        'is_deceased' => true,
        'deceased_year' => 1992,
        'workplace_and_position' => '1992 йилда вафот этган (1-марказий поликлиника шифокори)',
    ]);
});

test('noto\'g\'ri relationship_type rad etiladi', function () {
    $data = [
        'relatives' => [
            [
                'relationship_type' => 'ўғлим',
                'full_name_cyr' => 'Тестов Тест Тестович',
                'birth_year' => 2000,
                'birth_place' => 'Хоразм вилояти, Урганч шаҳри',
                'is_deceased' => false,
                'workplace_and_position' => 'Талаба',
                'residence_full' => 'Хоразм вилояти, Урганч шаҳри',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('employees.relatives.save', $this->employee->id), $data);

    $response->assertSessionHasErrors('relatives.0.relationship_type');
});

test('initsial bilan qarinosh ismi rad etiladi', function () {
    $data = [
        'relatives' => [
            [
                'relationship_type' => 'Ўғли',
                'full_name_cyr' => 'Тестов А.Т.',
                'birth_year' => 2000,
                'birth_place' => 'Хоразм вилояти, Урганч шаҳри',
                'is_deceased' => false,
                'workplace_and_position' => 'Талаба',
                'residence_full' => 'Хоразм вилояти, Урганч шаҳри',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('employees.relatives.save', $this->employee->id), $data);

    $response->assertSessionHasErrors('relatives.0.full_name_cyr');
});

test('vafot etgan qarinosh uchun yil ko\'rsatilishi shart', function () {
    $data = [
        'relatives' => [
            [
                'relationship_type' => 'Отаси',
                'full_name_cyr' => 'Тестов Тест Тестович',
                'birth_year' => 1945,
                'birth_place' => 'Хоразм вилояти, Урганч тумани',
                'is_deceased' => true,
                'deceased_year' => null,
                'former_position' => 'Шифокор',
                'residence_full' => 'Хоразм вилояти, Урганч шаҳри',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('employees.relatives.save', $this->employee->id), $data);

    $response->assertSessionHasErrors('relatives.0.deceased_year');
});
