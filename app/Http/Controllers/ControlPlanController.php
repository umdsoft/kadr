<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ControlPlans\SaveControlPlanItemsAction;
use App\Actions\ControlPlans\UpdateOverdueStatusAction;
use App\Enums\ExecutionStatus;
use App\Exports\ControlPlanDocxExporter;
use App\Http\Requests\StoreControlPlanRequest;
use App\Http\Requests\UpdateControlPlanRequest;
use App\Models\Activity;
use App\Models\ControlPlan;
use App\Models\ControlPlanItem;
use App\Models\Organization;
use App\Models\User;
use App\Services\ControlPlanAccessService;
use App\Services\PositionDisplayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ControlPlanController extends Controller
{
    public function __construct(
        private ControlPlanAccessService $access,
        private PositionDisplayService $positionDisplay,
    ) {
        $this->authorizeResource(ControlPlan::class, 'control_plan', [
            'except' => ['index', 'show'],
        ]);
    }

    public function index(Request $request): Response
    {
        $plans = ControlPlan::with('creator')
            ->withCount('items')
            ->when($request->search, fn ($q, $s) => $q->whereLike('title', "%{$s}%")
                ->orWhereLike('document_number', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(25);

        return Inertia::render('ControlPlans/Index', [
            'plans' => $plans,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ControlPlans/Create', [
            'users' => User::whereNull('organization_id')->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::where('is_active', true)->orderBy('name_cyr')->get(['id', 'name_cyr']),
        ]);
    }

    public function store(StoreControlPlanRequest $request, SaveControlPlanItemsAction $saveItems): RedirectResponse
    {
        $validated = $request->validated();

        $plan = ControlPlan::create([
            'title' => $validated['title'],
            'document_number' => $validated['document_number'] ?? null,
            'document_date' => $validated['document_date'] ?? null,
            'status_date' => $validated['status_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $saveItems->execute($plan, $validated['items'] ?? []);

        return redirect()->route('control-plans.show', $plan->id)
            ->with('success', 'Назорат режа яратилди.');
    }

    public function show(string $id, UpdateOverdueStatusAction $updateOverdue): Response
    {
        $plan = ControlPlan::with([
            'creator',
            'items.responsibles.user.position',
            'items.responsibles.user.department',
            'items.documents.uploader',
        ])->findOrFail($id);

        $updateOverdue->execute($plan);

        $user = auth()->user();
        $isCreator = $plan->created_by === $user->id;
        $isSuperAdmin = $user->hasRole('super-admin');

        // Har bir band uchun: tahrirlash huquqi va lavozim matni
        foreach ($plan->items as $item) {
            $item->can_edit = $this->access->canEditItem($user, $item);

            foreach ($item->responsibles as $resp) {
                $resp->display_position = $this->positionDisplay->forResponsible($resp);
            }
        }

        return Inertia::render('ControlPlans/Show', [
            'plan' => $plan,
            'isCreator' => $isCreator || $isSuperAdmin,
        ]);
    }

    public function edit(string $id): Response
    {
        $plan = ControlPlan::with(['items.responsibles'])->findOrFail($id);

        return Inertia::render('ControlPlans/Edit', [
            'plan' => $plan,
            'users' => User::whereNull('organization_id')->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::where('is_active', true)->orderBy('name_cyr')->get(['id', 'name_cyr']),
        ]);
    }

    public function update(
        UpdateControlPlanRequest $request,
        string $id,
        SaveControlPlanItemsAction $saveItems,
    ): RedirectResponse {
        $plan = ControlPlan::findOrFail($id);
        $validated = $request->validated();

        $plan->update([
            'title' => $validated['title'],
            'document_number' => $validated['document_number'] ?? null,
            'document_date' => $validated['document_date'] ?? null,
            'status' => $validated['status'],
            'status_date' => $validated['status_date'] ?? null,
        ]);

        $saveItems->execute($plan, $validated['items'] ?? [], replace: true);

        return redirect()->route('control-plans.show', $plan->id)
            ->with('success', 'Назорат режа янгиланди.');
    }

    public function destroy(string $id): RedirectResponse
    {
        ControlPlan::findOrFail($id)->delete();

        return redirect()->route('control-plans.index')
            ->with('success', 'Назорат режа ўчирилди.');
    }

    public function showItem(string $item): Response
    {
        $planItem = ControlPlanItem::with([
            'plan',
            'responsibles.user.position',
            'responsibles.user.department',
            'documents.uploader',
        ])->findOrFail($item);

        $activities = Activity::where('subject_type', ControlPlanItem::class)
            ->where('subject_id', $planItem->id)
            ->with('causer')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('ControlPlans/ItemShow', [
            'item' => $planItem,
            'activities' => $activities,
            'canEdit' => $this->access->canEditItem(auth()->user(), $planItem),
        ]);
    }

    public function updateItemStatus(Request $request, string $item): RedirectResponse
    {
        $planItem = ControlPlanItem::with('responsibles', 'plan')->findOrFail($item);

        abort_unless($this->access->canEditItem(auth()->user(), $planItem), 403);

        $validated = $request->validate([
            'execution_status' => ['required', 'in:'.ExecutionStatus::values()],
            'execution_report' => ['nullable', 'string'],
        ]);

        $planItem->update($validated);

        return back()->with('success', 'Банд ҳолати янгиланди.');
    }

    public function export(string $controlPlan, ControlPlanDocxExporter $exporter): BinaryFileResponse
    {
        $plan = ControlPlan::findOrFail($controlPlan);
        $path = $exporter->export($plan);

        return response()->download($path, $exporter->downloadName($plan))->deleteFileAfterSend(true);
    }
}
