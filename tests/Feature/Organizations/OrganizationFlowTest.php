<?php

declare(strict_types=1);

use App\Models\ControlPlanItem;
use App\Models\Department;
use App\Models\ItemResponsible;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(DepartmentSeeder::class);

    $this->root = Department::whereNull('parent_id')->orderBy('id')->first();
    $this->kompleks = Department::where('type', 'kompleks')->where('parent_id', $this->root->id)->first();

    $this->mudir = User::create([
        'name' => 'Котибият мудири', 'login' => 'mudir1',
        'password' => bcrypt('parol1234'), 'department_id' => $this->kompleks->id,
    ]);
    $this->mudir->assignRole('kotibyat-mudiri');
});

it('tashkilotni faqat admin yaratadi, kotibyat yarata olmaydi', function () {
    // Kotibyat endi tashkilot yarата olmaydi (admin vazifasi)
    $this->actingAs($this->mudir)
        ->post('/organizations', ['name_cyr' => 'Котибият мактаби', 'inn' => '111111111'])
        ->assertForbidden();

    // Admin (tuman-admin) yaratadi
    $admin = User::create([
        'name' => 'Туман админ', 'login' => 'tuman1',
        'password' => bcrypt('parol1234'), 'department_id' => $this->root->id,
    ]);
    $admin->assignRole('tuman-admin');

    $this->actingAs($admin)
        ->post('/organizations', [
            'name_cyr' => 'Админ мактаби', 'inn' => '123456789', 'phone' => '+998620000000',
        ])
        ->assertRedirect();

    $org = Organization::where('name_cyr', 'Админ мактаби')->first();
    expect($org)->not->toBeNull()
        ->and($org->hokimlik_id)->toBe($this->root->id)
        ->and($org->created_by)->toBe($admin->id);
});

it('tashkilotga admin login admin tomonidan ochiladi', function () {
    $admin = User::create([
        'name' => 'Туман админ', 'login' => 'tuman2',
        'password' => bcrypt('parol1234'), 'department_id' => $this->root->id,
    ]);
    $admin->assignRole('tuman-admin');

    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Тест ташкилот', 'created_by' => $admin->id,
    ]);

    // Kotibyat login ocha olmaydi (403)
    $this->actingAs($this->mudir)
        ->post("/organizations/{$org->id}/users", [
            'name' => 'X', 'login' => 'x1', 'password' => 'parol1234', 'role' => 'tashkilot-admin',
        ])
        ->assertForbidden();

    // Admin ochadi
    $this->actingAs($admin)
        ->post("/organizations/{$org->id}/users", [
            'name' => 'Ташкилот админи', 'login' => 'orgadmin1',
            'password' => 'parol1234', 'role' => 'tashkilot-admin',
        ])
        ->assertRedirect();

    $orgAdmin = User::where('login', 'orgadmin1')->first();
    expect($orgAdmin)->not->toBeNull()
        ->and($orgAdmin->organization_id)->toBe($org->id)
        ->and($orgAdmin->hasRole('tashkilot-admin'))->toBeTrue();
});

it('topshiriqni tashkilotga biriktira oladi va org admin ko\'radi', function () {
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Тест ташкилот', 'created_by' => $this->mudir->id,
    ]);
    $orgAdmin = User::create([
        'name' => 'Org Admin', 'login' => 'orgadmin2',
        'password' => bcrypt('parol1234'), 'organization_id' => $org->id,
    ]);
    $orgAdmin->assignRole('tashkilot-admin');

    // Mudir topshiriq yaratadi va tashkilotga biriktiradi
    $this->actingAs($this->mudir)
        ->post('/topshiriqlar', [
            'title' => 'Кўкаламзорлаштириш',
            'task_description' => 'Ҳудудни тозалаш ва кўкаламзорлаштириш',
            'deadline' => '2026-12-31',
            'assignee_type' => 'organization',
            'assignee_id' => $org->id,
        ])
        ->assertRedirect();

    $task = ControlPlanItem::where('source', 'standalone')->first();
    expect($task)->not->toBeNull()
        ->and($task->title)->toBe('Кўкаламзорлаштириш')
        ->and($task->hokimlik_id)->toBe($this->root->id);

    // Org admin o'ziga kelgan topshiriqni ko'radi
    $this->actingAs($orgAdmin)
        ->get('/topshiriqlar')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Tasks/Index')
            ->where('isOrgUser', true)
            ->has('tasks.data', 1));

    // Org admin ijro hisobotini kiritadi
    $this->actingAs($orgAdmin)
        ->put("/topshiriqlar/{$task->id}/status", [
            'execution_status' => 'completed',
            'execution_report' => 'Бажарилди',
        ])
        ->assertRedirect();

    expect($task->fresh()->execution_status)->toBe('completed');
});

it('nazorat reja bandiga tashkilot masъul biriktiriladi', function () {
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Масъул ташкилот', 'created_by' => $this->mudir->id,
    ]);

    $this->actingAs($this->mudir)
        ->post('/control-plans', [
            'title' => 'Тест назорат режа',
            'document_number' => 'ПҚ-100',
            'items' => [[
                'item_number' => '1',
                'task_description' => 'Топшириқ мазмуни',
                'responsibles' => [
                    [
                        'assignee_type' => 'organization',
                        'assignee_id' => $org->id,
                        'responsible_name' => $org->name_cyr,
                        'is_primary' => true,
                    ],
                    [
                        'assignee_type' => 'user',
                        'assignee_id' => $this->mudir->id,
                        'responsible_name' => $this->mudir->name,
                        'is_primary' => false,
                    ],
                ],
            ]],
        ])
        ->assertRedirect();

    $primary = ItemResponsible::where('is_primary', true)->first();
    expect($primary)->not->toBeNull()
        ->and($primary->assignee_type->value)->toBe('organization')
        ->and($primary->assignee_id)->toBe($org->id)
        ->and($primary->responsible_name)->toBe('Масъул ташкилот');

    // Faqat bitta asosiy ijrochi bo'lishi kerak
    expect(ItemResponsible::where('is_primary', true)->count())->toBe(1)
        ->and(ItemResponsible::count())->toBe(2);
});

it('org admin ijro yuklaydi, faqat mudir/masъul nazoratdan yechadi (EDO)', function () {
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'EDO ташкилот', 'created_by' => $this->mudir->id,
    ]);
    $orgAdmin = User::create([
        'name' => 'EDO Admin', 'login' => 'edoadmin',
        'password' => bcrypt('parol1234'), 'organization_id' => $org->id,
    ]);
    $orgAdmin->assignRole('tashkilot-admin');

    // Mudir tashkilotga topshiriq beradi
    $this->actingAs($this->mudir)->post('/topshiriqlar', [
        'title' => 'EDO топшириқ', 'task_description' => 'Мазмун',
        'assignee_type' => 'organization', 'assignee_id' => $org->id,
    ])->assertRedirect();
    $task = ControlPlanItem::where('source', 'standalone')->first();

    // Org admin o'ziga kelgan topshiriqni ko'radi va ijro yuklaydi
    $this->actingAs($orgAdmin)->get("/topshiriqlar/{$task->id}")->assertOk();
    $this->actingAs($orgAdmin)->put("/topshiriqlar/{$task->id}/status", [
        'execution_status' => 'completed', 'execution_report' => 'Бажарилди',
    ])->assertRedirect();
    expect($task->fresh()->execution_status)->toBe('completed');

    // Org admin nazoratdan YECHA OLMAYDI (403)
    $this->actingAs($orgAdmin)->put("/topshiriqlar/{$task->id}/remove-control", ['reason' => 'x'])
        ->assertForbidden();

    // Yaratgan mudir nazoratdan yechadi
    $this->actingAs($this->mudir)->put("/topshiriqlar/{$task->id}/remove-control", ['reason' => 'Ижро бажарилди'])
        ->assertRedirect();
    $fresh = $task->fresh();
    expect($fresh->control_removed_at)->not->toBeNull()
        ->and($fresh->control_removed_by)->toBe($this->mudir->id)
        ->and($fresh->isUnderControl())->toBeFalse();
});

it('mustaqil topshiriq bir nechta masulga (org + xodim) biriktiriladi', function () {
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Кўп масъул ташкилот', 'created_by' => $this->mudir->id,
    ]);
    $xodim = User::create([
        'name' => 'Ходим', 'login' => 'multi-xodim',
        'password' => bcrypt('parol1234'), 'department_id' => $this->kompleks->id,
    ]);

    $this->actingAs($this->mudir)->post('/topshiriqlar', [
        'title' => 'Кўп масъулли топшириқ', 'task_description' => 'Мазмун',
        'responsibles' => [
            ['assignee_type' => 'organization', 'assignee_id' => $org->id, 'responsible_name' => $org->name_cyr, 'is_primary' => true],
            ['assignee_type' => 'user', 'assignee_id' => $xodim->id, 'responsible_name' => $xodim->name, 'is_primary' => false],
        ],
    ])->assertRedirect();

    $task = ControlPlanItem::where('source', 'standalone')->latest()->first();
    expect($task->responsibles()->count())->toBe(2)
        ->and($task->responsibles()->where('is_primary', true)->count())->toBe(1);
    $primary = $task->responsibles()->where('is_primary', true)->first();
    expect($primary->assignee_type->value)->toBe('organization')
        ->and($primary->assignee_id)->toBe($org->id);

    // Ikkala ijrochi ham o'z inboxida ko'radi
    $this->actingAs($xodim)->get('/topshiriqlar')
        ->assertInertia(fn ($p) => $p->has('tasks.data', 1));
});

it('mustaqil topshiriq asosiy hujjat va havola bilan yaratiladi', function () {
    Storage::fake('local');
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Ҳужжатли ташкилот', 'created_by' => $this->mudir->id,
    ]);

    $this->actingAs($this->mudir)->post('/topshiriqlar', [
        'title' => 'Ҳужжатли топшириқ', 'task_description' => 'Мазмун',
        'link' => 'https://lex.uz/docs/123',
        'responsibles' => [
            ['assignee_type' => 'organization', 'assignee_id' => $org->id, 'responsible_name' => $org->name_cyr, 'is_primary' => true],
        ],
        'files' => [UploadedFile::fake()->create('asos.pdf', 50, 'application/pdf')],
    ])->assertRedirect();

    $task = ControlPlanItem::where('source', 'standalone')->latest()->first();
    expect($task->link)->toBe('https://lex.uz/docs/123')
        ->and($task->documents()->whereNull('task_response_id')->count())->toBe(1);
});

it('nazorat reja bandi ichki ijrochining topshiriqlar inboxida korinadi', function () {
    // Ichki hokimlik xodimi (oddiy ijrochi)
    $xodim = User::create([
        'name' => 'Ходим Ижрочи', 'login' => 'xodim1',
        'password' => bcrypt('parol1234'), 'department_id' => $this->kompleks->id,
    ]);

    // Mudir nazorat reja yaratadi va bandga xodimni mas'ul qiladi
    $this->actingAs($this->mudir)->post('/control-plans', [
        'title' => 'Reja A', 'document_number' => 'ПҚ-200',
        'items' => [[
            'item_number' => '1', 'task_description' => 'Band mazmuni',
            'responsibles' => [[
                'assignee_type' => 'user', 'assignee_id' => $xodim->id,
                'responsible_name' => $xodim->name, 'is_primary' => true,
            ]],
        ]],
    ])->assertRedirect();

    $item = ControlPlanItem::where('source', 'control_plan')->first();
    expect($item)->not->toBeNull();

    // Xodim o'z inboxida control-plan bandini ko'radi
    $this->actingAs($xodim)->get('/topshiriqlar')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Tasks/Index')->has('tasks.data', 1));
});

it('org admin respond yuboradi → korib chiqilmoqda (submitted), execution_status uzgarmaydi', function () {
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Respond ташкилот', 'created_by' => $this->mudir->id,
    ]);
    $orgAdmin = User::create([
        'name' => 'Respond Admin', 'login' => 'respondadmin',
        'password' => bcrypt('parol1234'), 'organization_id' => $org->id,
    ]);
    $orgAdmin->assignRole('tashkilot-admin');

    $this->actingAs($this->mudir)->post('/topshiriqlar', [
        'title' => 'Respond топшириқ', 'task_description' => 'Мазмун',
        'assignee_type' => 'organization', 'assignee_id' => $org->id,
    ])->assertRedirect();
    $task = ControlPlanItem::where('source', 'standalone')->first();
    $originalStatus = $task->execution_status;

    // Org javob yuboradi (faqat matn) → submitted, ijro statusi tegilmaydi
    $this->actingAs($orgAdmin)->post("/topshiriqlar/{$task->id}/respond", [
        'execution_report' => 'Ижро қилинди',
    ])->assertRedirect();

    $fresh = $task->fresh();
    expect($fresh->review_status->value)->toBe('submitted')
        ->and($fresh->execution_report)->toBe('Ижро қилинди')
        ->and($fresh->execution_status)->toBe($originalStatus)
        ->and($fresh->submitted_at)->not->toBeNull();
});

it('org admin bir nechta fayl bilan javob yuboradi', function () {
    Storage::fake('local');
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Multifile ташкилот', 'created_by' => $this->mudir->id,
    ]);
    $orgAdmin = User::create([
        'name' => 'Multifile Admin', 'login' => 'multifile',
        'password' => bcrypt('parol1234'), 'organization_id' => $org->id,
    ]);
    $orgAdmin->assignRole('tashkilot-admin');

    $this->actingAs($this->mudir)->post('/topshiriqlar', [
        'title' => 'Multifile топшириқ', 'task_description' => 'Мазмун',
        'assignee_type' => 'organization', 'assignee_id' => $org->id,
    ])->assertRedirect();
    $task = ControlPlanItem::where('source', 'standalone')->first();

    $this->actingAs($orgAdmin)->post("/topshiriqlar/{$task->id}/respond", [
        'execution_report' => 'Ижро',
        'files' => [
            UploadedFile::fake()->create('a.pdf', 50, 'application/pdf'),
            UploadedFile::fake()->create('b.pdf', 50, 'application/pdf'),
        ],
    ])->assertRedirect();

    expect($task->fresh()->documents()->count())->toBe(2)
        ->and($task->fresh()->review_status->value)->toBe('submitted');

    // Javob fayllari "Биriktirilган файллар"да эмас, timeline'да жавобга бириктирилган
    $this->actingAs($orgAdmin)->get("/topshiriqlar/{$task->id}")
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Tasks/Show')
            ->has('task.documents', 0)            // асосий файл йўқ
            ->has('task.timeline', 1)             // битта жавоб
            ->has('task.timeline.0.documents', 2)); // жавобга 2 файл

    // Javob topshiriq bergan (mudir) inbox/qo'ng'irog'iga tushadi
    $this->actingAs($this->mudir)->get('/topshiriqlar')->assertOk();
});

it('tasdiqlash oqimi: org yuboradi, mudir tasdiqlaydi/qaytaradi (EDO)', function () {
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Approval ташкилот', 'created_by' => $this->mudir->id,
    ]);
    $orgAdmin = User::create([
        'name' => 'Approval Admin', 'login' => 'approvaladmin',
        'password' => bcrypt('parol1234'), 'organization_id' => $org->id,
    ]);
    $orgAdmin->assignRole('tashkilot-admin');

    $this->actingAs($this->mudir)->post('/topshiriqlar', [
        'title' => 'Approval топшириқ', 'task_description' => 'Мазмун',
        'assignee_type' => 'organization', 'assignee_id' => $org->id,
    ])->assertRedirect();
    $task = ControlPlanItem::where('source', 'standalone')->first();

    // Org "bajarildi" deb belgilaydi → tasdiqqa yuboriladi
    $this->actingAs($orgAdmin)->put("/topshiriqlar/{$task->id}/status", [
        'execution_status' => 'completed', 'execution_report' => 'Бажарилди',
    ])->assertRedirect();
    expect($task->fresh()->review_status->value)->toBe('submitted');

    // Org tasdiqlay olmaydi (403)
    $this->actingAs($orgAdmin)->put("/topshiriqlar/{$task->id}/approve")->assertForbidden();

    // Mudir nav badge'da pending_approvals ko'radi
    $this->actingAs($this->mudir)->get('/topshiriqlar')->assertOk();

    // Mudir qaytaradi → returned + in_progress
    $this->actingAs($this->mudir)->put("/topshiriqlar/{$task->id}/return", ['comment' => 'Тўлдиринг'])
        ->assertRedirect();
    $fresh = $task->fresh();
    expect($fresh->review_status->value)->toBe('returned')
        ->and($fresh->execution_status)->toBe('in_progress')
        ->and($fresh->review_comment)->toBe('Тўлдиринг');

    // Org qayta yuboradi, mudir tasdiqlaydi
    $this->actingAs($orgAdmin)->put("/topshiriqlar/{$task->id}/status", ['execution_status' => 'completed'])->assertRedirect();
    $this->actingAs($this->mudir)->put("/topshiriqlar/{$task->id}/approve")->assertRedirect();
    expect($task->fresh()->review_status->value)->toBe('approved');
});

it('topshiriqlar index filtr kartalari va counts beradi', function () {
    // 2 ta standalone topshiriq (mudir yaratadi)
    ControlPlanItem::create([
        'source' => 'standalone', 'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'created_by' => $this->mudir->id, 'title' => 'T1', 'task_description' => 'x', 'execution_status' => 'not_started',
    ]);
    ControlPlanItem::create([
        'source' => 'standalone', 'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'created_by' => $this->mudir->id, 'title' => 'T2', 'task_description' => 'x', 'execution_status' => 'completed',
    ]);

    $this->actingAs($this->mudir)->get('/topshiriqlar')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Tasks/Index')
            ->where('filter', 'all')
            ->where('counts.all', 2)
            ->where('counts.pending', 1)
            ->where('counts.completed', 1));

    $this->actingAs($this->mudir)->get('/topshiriqlar?f=pending')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('filter', 'pending')->has('tasks.data', 1));
});

it('kotibyat dashboard yuklanadi (KPI + tashkilot kesimi)', function () {
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Dashboard ташкилот', 'created_by' => $this->mudir->id,
    ]);
    $this->actingAs($this->mudir)->post('/topshiriqlar', [
        'title' => 'Dashboard топшириқ', 'task_description' => 'Мазмун',
        'deadline' => now()->addDays(3)->format('Y-m-d'),
        'assignee_type' => 'organization', 'assignee_id' => $org->id,
    ])->assertRedirect();

    $this->actingAs($this->mudir)->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('mode', 'kotibyat')
            ->has('kpi')
            ->has('status_chart')
            ->has('due_soon')
            ->has('pending_tasks')
            ->has('by_organization'));
});

it('boshqa kompleks mudiri tashkilotni tahrirlay olmaydi (403)', function () {
    $org = Organization::create([
        'hokimlik_id' => $this->root->id, 'kompleks_id' => $this->kompleks->id,
        'name_cyr' => 'Тест ташкилот', 'created_by' => $this->mudir->id,
    ]);

    $otherKompleks = Department::where('type', 'kompleks')
        ->where('parent_id', $this->root->id)->where('id', '!=', $this->kompleks->id)->first();
    $otherMudir = User::create([
        'name' => 'Бошқа мудир', 'login' => 'mudir2',
        'password' => bcrypt('parol1234'), 'department_id' => $otherKompleks->id,
    ]);
    $otherMudir->assignRole('kotibyat-mudiri');

    $this->actingAs($otherMudir)
        ->put("/organizations/{$org->id}", ['name_cyr' => 'Ўзгартирилди'])
        ->assertForbidden();
});
