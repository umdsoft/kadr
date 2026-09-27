<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Appeals\CreateAppealAction;
use App\Actions\Appeals\RecordCouncilDecisionAction;
use App\Http\Requests\Appeals\RecordDecisionRequest;
use App\Http\Requests\Appeals\StoreAppealRequest;
use App\Models\AppealCategory;
use App\Models\CitizenAppeal;
use App\Models\Mahalla;
use App\Models\YouthMeeting;
use App\Services\Appeals\AppealRoutingService;
use App\Services\Appeals\AppealStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CitizenAppealController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CitizenAppeal::class, 'appeal');
    }

    public function index(Request $request): Response
    {
        $appeals = CitizenAppeal::query()
            ->with(['category', 'subCategory', 'mahalla', 'activeAssignment'])
            ->when($request->search, fn ($q, $s) => $q->whereLike('applicant_name', "%{$s}%")
                ->orWhereLike('body', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn ($q, $p) => $q->where('priority', $p))
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->mahalla_id, fn ($q, $m) => $q->where('mahalla_id', $m))
            ->orderByDesc('submitted_at')
            ->paginate(25);

        return Inertia::render('Appeals/Index', [
            'appeals' => $appeals,
            'filters' => $request->only(['search', 'status', 'priority', 'category_id', 'mahalla_id']),
            'categories' => AppealCategory::roots()->with('children')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Appeals/Create', $this->formData());
    }

    public function store(StoreAppealRequest $request, CreateAppealAction $action): RedirectResponse
    {
        $appeal = $action->execute($request->validated());

        return redirect()->route('appeals.show', $appeal->id)
            ->with('success', 'Мурожаат қабул қилинди.');
    }

    public function show(CitizenAppeal $appeal): Response
    {
        $appeal->load([
            'category', 'subCategory', 'mahalla', 'meeting',
            'creator', 'assignments.assigner',
            'decisions.council.mahalla', 'decisions.decider',
            'documents.uploader', 'comments.author',
            'statusHistory.changer',
            'aiClassifications', 'aiDrafts',
            'feedback',
        ]);

        // Active assignment'ni resolve
        $activeAssignee = null;
        if ($appeal->activeAssignment) {
            $resolved = $appeal->activeAssignment->resolveAssignee();
            $activeAssignee = $resolved ? [
                'type' => $appeal->activeAssignment->assignee_type,
                'name' => $this->resolveName($resolved, $appeal->activeAssignment->assignee_type),
                'id' => $resolved->id,
            ] : null;
        }

        return Inertia::render('Appeals/Show', [
            'appeal' => $appeal,
            'active_assignee' => $activeAssignee,
        ]);
    }

    public function edit(CitizenAppeal $appeal): Response
    {
        return Inertia::render('Appeals/Edit', [
            ...$this->formData(),
            'appeal' => $appeal,
        ]);
    }

    public function update(StoreAppealRequest $request, CitizenAppeal $appeal): RedirectResponse
    {
        $appeal->update($request->validated());

        return redirect()->route('appeals.show', $appeal->id)
            ->with('success', 'Мурожаат янгиланди.');
    }

    public function destroy(CitizenAppeal $appeal): RedirectResponse
    {
        $appeal->delete();

        return redirect()->route('appeals.index')
            ->with('success', 'Мурожаат ўчирилди.');
    }

    /** Manual triage va routing — operator murojaatga kategoriya berdi */
    public function triage(
        Request $request,
        CitizenAppeal $appeal,
        AppealRoutingService $router,
        AppealStatusService $statusService,
    ): RedirectResponse {
        $this->authorize('assign', $appeal);

        $validated = $request->validate([
            'category_id' => ['required', 'string', 'exists:appeal_categories,id'],
            'sub_category_id' => ['nullable', 'string', 'exists:appeal_categories,id'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
        ]);

        // Уч босқичли ёзув битта транзаксияда — ўрта йўлда хато бўлса номувофиқ ҳолат қолмаслиги учун.
        DB::transaction(function () use ($appeal, $validated, $statusService, $router): void {
            $appeal->update($validated);
            $statusService->transition($appeal, 'triaged', 'Manual triage by operator');
            $router->route($appeal->refresh());
        });

        return back()->with('success', 'Мурожаат йўналтирилди.');
    }

    /** Mahalla yettiligi qarori */
    public function recordDecision(
        RecordDecisionRequest $request,
        CitizenAppeal $appeal,
        RecordCouncilDecisionAction $action,
    ): RedirectResponse {
        $this->authorize('decide', $appeal);

        $action->execute($appeal, $request->validated());

        return back()->with('success', 'Қарор қайд этилди.');
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'categories' => AppealCategory::roots()->with('children')->orderBy('sort_order')->get(),
            'mahallas' => Mahalla::orderBy('name_cyr')->get(['id', 'district_id', 'name_cyr']),
            'meetings' => YouthMeeting::orderByDesc('meeting_date')->limit(20)->get(['id', 'meeting_date', 'location']),
        ];
    }

    private function resolveName($model, string $type): string
    {
        return match ($type) {
            'council' => $model->mahalla?->name_cyr.' (yettilik)',
            'department' => $model->name_cyr,
            'user' => $model->name,
            default => '—',
        };
    }
}
