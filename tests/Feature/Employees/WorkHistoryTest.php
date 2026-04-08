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

test('mehnat faoliyati muvaffaqiyatli saqlanadi', function () {
    $data = [
        'work_history' => [
            [
                'start_year' => 2010,
                'end_year' => 2015,
                'organization_full' => 'Урганч давлат университети',
                'position_full' => 'Иқтисодиёт факультети ўқитувчиси',
            ],
            [
                'start_year' => 2015,
                'end_year' => null,
                'organization_full' => 'Хоразм вилояти ҳокимлиги',
                'position_full' => 'Кадрлар бўлими бош мутахассиси',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('employees.work-history.save', $this->employee->id), $data);

    $response->assertRedirect();
    $this->assertDatabaseCount('employee_work_history', 2);
    $this->assertDatabaseHas('employee_work_history', [
        'employee_id' => $this->employee->id,
        'start_year' => 2010,
        'sort_order' => 0,
    ]);
});

test('qisqartirmali tashkilot nomi rad etiladi', function () {
    $data = [
        'work_history' => [
            [
                'start_year' => 2010,
                'end_year' => 2015,
                'organization_full' => 'Тош. давлат университети',
                'position_full' => 'Ўқитувчи',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('employees.work-history.save', $this->employee->id), $data);

    $response->assertSessionHasErrors('work_history.0.organization_full');
});

test('bo\'sh mehnat faoliyati rad etiladi', function () {
    $data = ['work_history' => []];

    $response = $this->actingAs($this->user)
        ->put(route('employees.work-history.save', $this->employee->id), $data);

    $response->assertSessionHasErrors('work_history');
});
