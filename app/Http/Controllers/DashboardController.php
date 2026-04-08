<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::whereNull('deleted_at')->count(),
                'archived_employees' => Employee::onlyTrashed()->count(),
                'departments_count' => Department::where('is_active', true)->count(),
            ],
            'by_department' => DB::table('employees')
                ->select('departments.name_cyr as department', DB::raw('count(*) as count'))
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->whereNull('employees.deleted_at')
                ->groupBy('departments.name_cyr')
                ->get(),
            'by_education' => DB::table('employees')
                ->select('education_level as level', DB::raw('count(*) as count'))
                ->whereNull('deleted_at')
                ->groupBy('education_level')
                ->get(),
            'recent_activity' => Activity::with('causer')
                ->latest()
                ->take(10)
                ->get()
                ->map(function (Activity $a): array {
                    /** @var \App\Models\User|null $causer */
                    $causer = $a->causer;

                    return [
                        'id' => $a->id,
                        'description' => $a->description,
                        'subject_type' => class_basename((string) $a->subject_type),
                        'causer' => $causer !== null ? $causer->name : 'Тизим',
                        'created_at' => $a->created_at?->format('d.m.Y H:i'),
                        'properties' => $a->properties,
                    ];
                }),
        ]);
    }
}
