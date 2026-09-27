<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Mahalla;
use App\Models\MahallaCouncil;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MahallaCouncilController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MahallaCouncil::class, 'council');
    }

    public function index(Request $request): Response
    {
        $councils = MahallaCouncil::with(['mahalla', 'members'])
            ->withCount('members')
            ->when($request->search, fn ($q, $s) => $q->whereHas('mahalla', fn ($mq) => $mq->whereLike('name_cyr', "%{$s}%")))
            ->orderBy('mahalla_id')
            ->paginate(25);

        return Inertia::render('Councils/Index', [
            'councils' => $councils,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Councils/Create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mahalla_id' => ['required', 'string', 'exists:mahallas,id', 'unique:mahalla_councils,mahalla_id'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'members' => ['nullable', 'array'],
            'members.*.user_id' => ['nullable', 'string', 'exists:users,id'],
            'members.*.full_name' => ['required', 'string', 'max:255'],
            'members.*.role' => ['required', Rule::in(['rais', 'imom', 'yoshlar', 'ayollar', 'posbon', 'maktab', 'soliq', 'boshqa'])],
            'members.*.phone' => ['nullable', 'string', 'max:20'],
        ]);

        $council = DB::transaction(function () use ($validated) {
            $council = MahallaCouncil::create([
                'mahalla_id' => $validated['mahalla_id'],
                'name' => $validated['name'] ?? 'Маҳалла еттилиги',
                'phone' => $validated['phone'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            foreach (($validated['members'] ?? []) as $member) {
                $council->members()->create($member + ['is_active' => true]);
            }

            return $council;
        });

        return redirect()->route('councils.show', $council->id)
            ->with('success', 'Маҳалла еттилиги яратилди.');
    }

    public function show(MahallaCouncil $council): Response
    {
        $council->load(['mahalla', 'members.user']);

        return Inertia::render('Councils/Show', ['council' => $council]);
    }

    public function edit(MahallaCouncil $council): Response
    {
        $council->load(['mahalla', 'members']);

        return Inertia::render('Councils/Edit', [
            ...$this->formData(),
            'council' => $council,
        ]);
    }

    public function update(Request $request, MahallaCouncil $council): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'members' => ['nullable', 'array'],
            'members.*.id' => ['nullable', 'string', 'exists:council_members,id'],
            'members.*.user_id' => ['nullable', 'string', 'exists:users,id'],
            'members.*.full_name' => ['required', 'string', 'max:255'],
            'members.*.role' => ['required', Rule::in(['rais', 'imom', 'yoshlar', 'ayollar', 'posbon', 'maktab', 'soliq', 'boshqa'])],
            'members.*.phone' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($council, $validated) {
            $council->update([
                'name' => $validated['name'] ?? $council->name,
                'phone' => $validated['phone'] ?? null,
                'is_active' => $validated['is_active'] ?? $council->is_active,
            ]);

            // A'zolarni qayta yaratish (oddiy approach — keyinchalik diff bilan optimize)
            $council->members()->delete();
            foreach (($validated['members'] ?? []) as $member) {
                unset($member['id']);
                $council->members()->create($member + ['is_active' => true]);
            }
        });

        return redirect()->route('councils.show', $council->id)
            ->with('success', 'Маълумот янгиланди.');
    }

    public function destroy(MahallaCouncil $council): RedirectResponse
    {
        $council->delete();

        return redirect()->route('councils.index')
            ->with('success', 'Маҳалла еттилиги ўчирилди.');
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'mahallas' => Mahalla::orderBy('name_cyr')->get(['id', 'district_id', 'name_cyr']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ];
    }
}
