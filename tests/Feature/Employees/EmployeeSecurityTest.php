<?php

declare(strict_types=1);

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * H2 — JSHSHIR/passport frontendga oqib chiqmasligi ($hidden).
 * M1 — xodim rasmi faqat avtorizatsiyalangan maршрут orqali (public disk emas).
 */
beforeEach(function () {
    Role::create(['name' => 'super-admin']);
    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
});

it('xodim serializatsiyasida JSHSHIR/passport YASHIRIN', function () {
    $employee = Employee::factory()->create();

    $array = $employee->fresh()->toArray();

    expect($array)->not->toHaveKey('jshshir')
        ->and($array)->not->toHaveKey('passport_series')
        ->and($array)->not->toHaveKey('passport_number')
        ->and($array)->not->toHaveKey('jshshir_hash');
});

it('xodimlar ro\'yxati (index) JSHSHIR/passport oqizmaydi', function () {
    Employee::factory()->create();

    $response = $this->actingAs($this->user)->get(route('employees.index'));

    $response->assertStatus(200);
    // Inertia payload (HTML) ichida ochiq JSHSHIR/passport bo'lmasligi kerak.
    $content = $response->getContent();
    expect($content)->not->toContain('passport_number')
        ->and($content)->not->toContain('jshshir_hash');
});

it('edit sahifasi maxfiy maydonlarni ko\'rinadigan qiladi (forma to\'ldirish uchun)', function () {
    $employee = Employee::factory()->create();

    $response = $this->actingAs($this->user)->get(route('employees.edit', $employee->id));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Employees/Edit')
            ->where('employee.passport_series', 'AB'));
});

it('xodim rasmi maршрути autentifikatsiyasiz kirishni rad etadi', function () {
    $employee = Employee::factory()->create();

    $this->get(route('employees.photo', $employee->id))
        ->assertRedirect(route('login'));
});

it('rasm avtorizatsiyalangan foydalanuvchiga beriladi', function () {
    Storage::fake('local');
    $path = UploadedFile::fake()->image('photo.jpg')->store('employee-photos', 'local');

    $employee = Employee::factory()->create();
    $employee->update(['photo_path' => $path]);

    $this->actingAs($this->user)
        ->get(route('employees.photo', $employee->id))
        ->assertOk();
});

it('photo_url accessor avtorizatsiyalangan maршрутни qaytaradi', function () {
    $employee = Employee::factory()->create(['photo_path' => 'employee-photos/x.jpg']);
    expect($employee->photo_url)->toBe(route('employees.photo', $employee->id));

    $noPhoto = Employee::factory()->create(['photo_path' => null]);
    expect($noPhoto->photo_url)->toBeNull();
});
