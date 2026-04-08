<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Ҳимояланган маршрутлар — фақат тизимга кирганлар учун
Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Аудит журнали
    Route::get('/audit', [AuditController::class, 'index'])
        ->name('audit.index')
        ->middleware('can:audit.view');

    // Ходимлар CRUD
    Route::resource('employees', EmployeeController::class);

    // 3-блок: Меҳнат фаолияти
    Route::put('employees/{employee}/work-history', [EmployeeController::class, 'saveWorkHistory'])
        ->name('employees.work-history.save');

    // 4-блок: Яқин қариндошлар
    Route::put('employees/{employee}/relatives', [EmployeeController::class, 'saveRelatives'])
        ->name('employees.relatives.save');

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
