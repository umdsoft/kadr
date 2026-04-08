<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%");
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        return Inertia::render('Audit/Index', [
            'activities' => $query->paginate(25)->through(function (Activity $a): array {
                /** @var \App\Models\User|null $causer */
                $causer = $a->causer;

                return [
                    'id' => $a->id,
                    'log_name' => $a->log_name,
                    'description' => $a->description,
                    'subject_type' => class_basename((string) $a->subject_type),
                    'subject_id' => $a->subject_id,
                    'causer' => $causer !== null ? $causer->name : 'Тизим',
                    'properties' => $a->properties,
                    'created_at' => $a->created_at?->format('d.m.Y H:i:s'),
                ];
            }),
            'filters' => $request->only(['search', 'subject_type']),
        ]);
    }
}
