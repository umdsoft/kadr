<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Support\ActivityTranslator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereLike('description', "%{$search}%");
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        return Inertia::render('Audit/Index', [
            'activities' => $query->paginate(25)->withQueryString()->through(function (Activity $a): array {
                /** @var User|null $causer */
                $causer = $a->causer;

                $subject = class_basename((string) $a->subject_type);

                return [
                    'id' => $a->id,
                    'log_name' => $a->log_name,
                    'description' => ActivityTranslator::event($a->description),
                    'subject_type' => ActivityTranslator::subject($subject),
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
