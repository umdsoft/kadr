<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\YoshlarYetakchilari\CreateYyAction;
use App\Actions\YoshlarYetakchilari\UpdateYyAction;
use App\Http\Requests\YoshlarYetakchilari\StoreYyRequest;
use App\Http\Requests\YoshlarYetakchilari\UpdateYyRequest;
use App\Models\Mahalla;
use App\Models\User;
use App\Models\YoshlarYetakchisi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YoshlarYetakchisiController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(YoshlarYetakchisi::class, 'yoshlar_yetakchi');
    }

    public function index(Request $request): Response
    {
        $list = YoshlarYetakchisi::with(['user', 'mahalla'])
            ->withCount('events')
            ->when($request->search, fn ($q, $s) => $q->whereLike('full_name_cyr', "%{$s}%"))
            ->when($request->is_active !== null, fn ($q) => $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)))
            ->orderByDesc('created_at')
            ->paginate(25);

        return Inertia::render('YoshlarYetakchilari/Index', [
            'items' => $list,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('YoshlarYetakchilari/Create', $this->formData());
    }

    public function store(StoreYyRequest $request, CreateYyAction $action): RedirectResponse
    {
        $action->execute($request->validated(), auth()->id());

        return redirect()->route('yoshlar-yetakchilari.index')
            ->with('success', 'Ёшлар етакчиси қўшилди.');
    }

    public function show(YoshlarYetakchisi $yoshlarYetakchi): Response
    {
        $yoshlarYetakchi->load(['user', 'mahalla', 'creator', 'events.creator']);

        return Inertia::render('YoshlarYetakchilari/Show', ['item' => $yoshlarYetakchi]);
    }

    public function edit(YoshlarYetakchisi $yoshlarYetakchi): Response
    {
        return Inertia::render('YoshlarYetakchilari/Edit', [
            ...$this->formData(),
            'item' => $yoshlarYetakchi,
        ]);
    }

    public function update(
        UpdateYyRequest $request,
        YoshlarYetakchisi $yoshlarYetakchi,
        UpdateYyAction $action,
    ): RedirectResponse {
        $action->execute($yoshlarYetakchi, $request->validated());

        return redirect()->route('yoshlar-yetakchilari.show', $yoshlarYetakchi->id)
            ->with('success', 'Маълумот янгиланди.');
    }

    public function destroy(YoshlarYetakchisi $yoshlarYetakchi): RedirectResponse
    {
        $yoshlarYetakchi->delete();

        return redirect()->route('yoshlar-yetakchilari.index')
            ->with('success', 'Ёшлар етакчиси ўчирилди.');
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
