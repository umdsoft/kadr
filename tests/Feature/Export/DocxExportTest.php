<?php

use App\Exports\MalumotnomaDocxExporter;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkHistory;
use App\Models\Relative;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::create(['name' => 'employee.view']);
    Permission::create(['name' => 'employee.export']);

    $role = Role::create(['name' => 'hr-staff']);
    $role->givePermissionTo(['employee.view', 'employee.export']);

    $this->user = User::factory()->create();
    $this->user->assignRole('hr-staff');
});

test('docx fayl muvaffaqiyatli generatsiya qilinadi', function () {
    $employee = Employee::factory()->create([
        'last_name_cyr' => 'Эшматов',
        'first_name_cyr' => 'Эшмат',
        'middle_name_cyr' => 'Тошматович',
        'position_start_date' => '2007-10-25',
    ]);

    WorkHistory::create([
        'employee_id' => $employee->id,
        'start_year' => 2005,
        'end_year' => null,
        'organization_full' => 'Хоразм вилояти ҳокимлиги',
        'position_full' => 'кадрлар бўлими бош мутахассиси',
        'sort_order' => 0,
    ]);

    Relative::create([
        'employee_id' => $employee->id,
        'relationship_type' => 'Турмуш ўртоғи',
        'full_name_cyr' => 'Эшматова Гулнора Рустамовна',
        'birth_year' => 1985,
        'birth_place' => 'Хоразм вилояти, Урганч шаҳри',
        'is_deceased' => false,
        'workplace_and_position' => 'Урганч шаҳар 15-мактаб ўқитувчиси',
        'residence_full' => 'Хоразм вилояти, Урганч шаҳри, Навоий кўчаси 10-уй',
    ]);

    $exporter = app(MalumotnomaDocxExporter::class);
    $path = $exporter->export($employee);

    expect($path)->toEndWith('.docx')
        ->and(file_exists($path))->toBeTrue()
        ->and(filesize($path))->toBeGreaterThan(0);

    // Тозалаш
    @unlink($path);
});

test('docx fayl nomi to\'g\'ri formatda', function () {
    $employee = Employee::factory()->create([
        'last_name_cyr' => 'Каримов',
        'first_name_cyr' => 'Исмоил',
    ]);

    $exporter = app(MalumotnomaDocxExporter::class);
    $filename = $exporter->generateFilename($employee);

    $today = now()->format('Y-m-d');
    expect($filename)->toBe("Malumotnoma_Каримов_Исмоил_{$today}.docx");
});

test('eksport route ishlaydi va docx qaytaradi', function () {
    $employee = Employee::factory()->create();

    $response = $this->actingAs($this->user)
        ->get(route('employees.export.malumotnoma', $employee->id));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
});
