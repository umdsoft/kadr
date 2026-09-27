<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\HokimYordamchilari\CreateHyAction;
use App\Actions\HokimYordamchilari\UpdateHyAction;
use App\Http\Requests\HokimYordamchilari\StoreHyRequest;
use App\Http\Requests\HokimYordamchilari\UpdateHyRequest;
use App\Models\HokimYordamchisi;
use App\Models\Mahalla;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HokimYordamchisiController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(HokimYordamchisi::class, 'hokim_yordamchisi');
    }

    public function index(Request $request): Response
    {
        $list = HokimYordamchisi::with(['user', 'mahalla', 'creator'])
            ->withCount('assignments')
            ->when($request->search, fn ($q, $s) => $q->whereLike('full_name_cyr', "%{$s}%"))
            ->when($request->direction, fn ($q, $d) => $q->where('direction', $d))
            ->when($request->is_active !== null, fn ($q) => $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)))
            ->orderByDesc('created_at')
            ->paginate(25);

        return Inertia::render('HokimYordamchilari/Index', [
            'items' => $list,
            'filters' => $request->only(['search', 'direction', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('HokimYordamchilari/Create', $this->formData());
    }

    public function store(StoreHyRequest $request, CreateHyAction $action): RedirectResponse
    {
        $action->execute($request->validated(), auth()->id());

        return redirect()->route('hokim-yordamchilari.index')
            ->with('success', 'Ҳоким ёрдамчиси қўшилди.');
    }

    public function show(HokimYordamchisi $hokimYordamchisi): Response
    {
        $hokimYordamchisi->load(['user', 'mahalla', 'creator', 'assignments.creator']);

        return Inertia::render('HokimYordamchilari/Show', [
            'item' => $hokimYordamchisi,
        ]);
    }

    public function edit(HokimYordamchisi $hokimYordamchisi): Response
    {
        return Inertia::render('HokimYordamchilari/Edit', [
            ...$this->formData(),
            'item' => $hokimYordamchisi->load(['user', 'mahalla']),
        ]);
    }

    public function update(
        UpdateHyRequest $request,
        HokimYordamchisi $hokimYordamchisi,
        UpdateHyAction $action,
    ): RedirectResponse {
        $action->execute($hokimYordamchisi, $request->validated());

        return redirect()->route('hokim-yordamchilari.show', $hokimYordamchisi->id)
            ->with('success', 'Маълумот янгиланди.');
    }

    public function destroy(HokimYordamchisi $hokimYordamchisi): RedirectResponse
    {
        $hokimYordamchisi->delete();

        return redirect()->route('hokim-yordamchilari.index')
            ->with('success', 'Ҳоким ёрдамчиси ўчирилди.');
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'mahallas' => Mahalla::orderBy('name_cyr')->get(['id', 'district_id', 'name_cyr']),
        ];
    }
}
