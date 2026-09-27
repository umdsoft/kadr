<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CitizenAppealController;
use App\Http\Controllers\ControlPlanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HokimYordamchisiController;
use App\Http\Controllers\ItemDocumentController;
use App\Http\Controllers\MahallaCouncilController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationUserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\YoshlarYetakchisiController;
use App\Http\Controllers\YouthMeetingController;
use Illuminate\Support\Facades\Route;

// Ҳимояланган маршрутлар — фақат тизимга кирганлар учун
Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Super-admin/viloyat-admin uchun tenant tanlovi
    Route::post('/tenant/switch', [TenantController::class, 'switch'])->name('tenant.switch');

    // Аудит журнали
    Route::get('/audit', [AuditController::class, 'index'])
        ->name('audit.index')
        ->middleware('can:audit.view');

    // Ходимлар CRUD
    Route::resource('employees', EmployeeController::class);

    // Ходим расми — авторизацияланган (public диск эмас)
    Route::get('employees/{employee}/photo', [EmployeeController::class, 'photo'])
        ->name('employees.photo');

    // 3-блок: Меҳнат фаолияти
    Route::put('employees/{employee}/work-history', [EmployeeController::class, 'saveWorkHistory'])
        ->name('employees.work-history.save');

    // 4-блок: Яқин қариндошлар
    Route::put('employees/{employee}/relatives', [EmployeeController::class, 'saveRelatives'])
        ->name('employees.relatives.save');

    // Назорат режа: банд кўриш/статус — resource dan oldin (route conflict oldini olish)
    Route::middleware('can:tadbirlar.view')->group(function () {
        Route::get('control-plans/items/{item}', [ControlPlanController::class, 'showItem'])
            ->name('control-plan-items.show');
        Route::put('control-plans/items/{item}/status', [ControlPlanController::class, 'updateItemStatus'])
            ->name('control-plan-items.update-status');
        Route::get('control-plans/{controlPlan}/export', [ControlPlanController::class, 'export'])
            ->name('control-plans.export');
    });

    // Ҳужжат алмашинуви (EDO) — рухсат controllerда ControlPlanAccessService орқали
    // (ташкилот масъуллари ҳам ўз топшириқларига ҳужжат юклай олиши учун).
    Route::post('control-plans/items/{item}/documents', [ItemDocumentController::class, 'store'])
        ->name('item-documents.store');
    Route::get('documents/{document}/download', [ItemDocumentController::class, 'download'])
        ->name('documents.download');
    Route::delete('documents/{document}', [ItemDocumentController::class, 'destroy'])
        ->name('documents.destroy');

    // Назорат режалар (CRUD) — Policy orqali authorization + index/show учун can:tadbirlar.view
    // (authorizeResource index/show'ни except қилган — акс ҳолда ташкилот фойдаланувчиси
    // рухсатсиз рўйхат/деталини кўра оларди).
    Route::resource('control-plans', ControlPlanController::class)
        ->middleware('can:tadbirlar.view');

    // Ҳоким ёрдамчилари моdули
    Route::resource('hokim-yordamchilari', HokimYordamchisiController::class)
        ->parameters(['hokim-yordamchilari' => 'hokim_yordamchisi']);

    // Ёшлар етакчилари моdули
    Route::resource('yoshlar-yetakchilari', YoshlarYetakchisiController::class)
        ->parameters(['yoshlar-yetakchilari' => 'yoshlar_yetakchi']);

    // Yoshlar uchrashuvlari
    Route::resource('meetings', YouthMeetingController::class);

    // Mahalla yettiligi
    Route::resource('councils', MahallaCouncilController::class);

    // Ташкилотлар (котибият мудири яратади ва бошқаради)
    Route::resource('organizations', OrganizationController::class);
    Route::post('organizations/{organization}/users', [OrganizationUserController::class, 'store'])
        ->name('organizations.users.store');
    Route::delete('organizations/{organization}/users/{user}', [OrganizationUserController::class, 'destroy'])
        ->name('organizations.users.destroy');

    // Мустақил топшириқлар (ходим ёки ташкилотга)
    Route::get('topshiriqlar', [TaskController::class, 'index'])->name('topshiriqlar.index');
    Route::get('topshiriqlar/create', [TaskController::class, 'create'])->name('topshiriqlar.create');
    Route::post('topshiriqlar', [TaskController::class, 'store'])->name('topshiriqlar.store');
    Route::get('topshiriqlar/{task}', [TaskController::class, 'show'])->name('topshiriqlar.show');
    Route::put('topshiriqlar/{task}/status', [TaskController::class, 'updateStatus'])
        ->name('topshiriqlar.update-status');
    Route::post('topshiriqlar/{task}/respond', [TaskController::class, 'respond'])
        ->name('topshiriqlar.respond');
    Route::put('topshiriqlar/{task}/remove-control', [TaskController::class, 'removeFromControl'])
        ->name('topshiriqlar.remove-control');
    Route::put('topshiriqlar/{task}/restore-control', [TaskController::class, 'restoreControl'])
        ->name('topshiriqlar.restore-control');
    // Тасдиқлаш оқими (EDO)
    Route::put('topshiriqlar/{task}/approve', [TaskController::class, 'approve'])
        ->name('topshiriqlar.approve');
    Route::put('topshiriqlar/{task}/return', [TaskController::class, 'returnForRework'])
        ->name('topshiriqlar.return');

    // Murojaatlar (citizen appeals)
    Route::resource('appeals', CitizenAppealController::class);
    Route::post('appeals/{appeal}/triage', [CitizenAppealController::class, 'triage'])->name('appeals.triage');
    Route::post('appeals/{appeal}/decisions', [CitizenAppealController::class, 'recordDecision'])->name('appeals.decisions.store');

    // Фойдаланувчилар бошқаруви — авторизация UserPolicy орқали
    // (ҳар амал: ruxsat + tenant + рол даражаси).
    Route::resource('users', UserController::class)
        ->except(['show']);

    // DOCX экспорт
    Route::get('employees/{employee}/export/malumotnoma', [ExportController::class, 'downloadMalumotnoma'])
        ->name('employees.export.malumotnoma');
});

// Каталог API — frontend dropdown lar учун
Route::prefix('api/catalogs')->name('api.catalogs.')->middleware('auth')->group(function () {
    Route::get('/regions', [CatalogController::class, 'regions'])->name('regions');
    Route::get('/regions/{region}/districts', [CatalogController::class, 'districts'])->name('districts');
    Route::get('/districts/{district}/mahallas', [CatalogController::class, 'mahallas'])->name('mahallas');
    Route::get('/nationalities', [CatalogController::class, 'nationalities'])->name('nationalities');
    Route::get('/specialties', [CatalogController::class, 'specialties'])->name('specialties');
    Route::get('/departments', [CatalogController::class, 'departments'])->name('departments');
    Route::get('/positions', [CatalogController::class, 'positions'])->name('positions');
    Route::get('/departments/{department}/positions', [CatalogController::class, 'positions'])->name('department.positions');
});
