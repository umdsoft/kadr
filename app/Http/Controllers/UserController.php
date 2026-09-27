<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use App\Support\Authorization\RoleHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct()
    {
        // Har bir amal UserPolicy orqali: index→viewAny, create/store→create,
        // edit/update→update, destroy→delete (ruxsat + tenant + rol darajasi).
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): Response
    {
        $users = User::with(['roles', 'department', 'position'])
            ->tap(fn (Builder $q) => $this->scopeToTenant($q))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w
                ->whereLike('name', "%{$s}%")
                ->orWhereLike('login', "%{$s}%")))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,login'],
            'password' => ['required', 'string', 'min:8'],
            'department_id' => ['required', 'string', 'exists:departments,id'],
            'position_id' => ['required', 'string', 'exists:positions,id'],
        ]);

        $position = Position::findOrFail($validated['position_id']);
        $roleName = $position->role_name ?? 'mutaxassis';

        /** @var User $actor */
        $actor = $request->user();
        $this->guardDepartmentTenant($actor, $validated['department_id']);
        $this->guardAssignableRole($actor, $roleName);

        $user = User::create([
            'name' => $validated['name'],
            'login' => $validated['login'],
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
        ]);

        $user->assignRole($roleName);

        return redirect()->route('users.index')->with('success', 'Фойдаланувчи яратилди.');
    }

    public function edit(User $user): Response
    {
        $user->load(['roles', 'department:id,parent_id,name_cyr', 'position']);

        return Inertia::render('Users/Edit', [
            'editUser' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'login')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'department_id' => ['required', 'string', 'exists:departments,id'],
            'position_id' => ['required', 'string', 'exists:positions,id'],
        ]);

        $position = Position::findOrFail($validated['position_id']);
        $roleName = $position->role_name ?? 'mutaxassis';

        /** @var User $actor */
        $actor = $request->user();
        $this->guardDepartmentTenant($actor, $validated['department_id']);
        $this->guardAssignableRole($actor, $roleName);

        $updateData = [
            'name' => $validated['name'],
            'login' => $validated['login'],
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Лавозим ўзгарса ролни ҳам янгилаш
        $user->syncRoles([$roleName]);

        return redirect()->route('users.index')->with('success', 'Фойдаланувчи янгиланди.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Ўзингизни ўчира олмайсиз.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Фойдаланувчи ўчирилди.');
    }

    /**
     * Index ro'yxatini joriy tenant bilan cheklash.
     * Cross-tenant admin (super/viloyat) — barcha foydalanuvchilar.
     */
    private function scopeToTenant(Builder $query): void
    {
        /** @var User $actor */
        $actor = auth()->user();

        if ($actor->hasRole('super-admin') || $actor->hasRole('viloyat-admin')) {
            return;
        }

        $tenantId = $actor->hokimlik_id;
        if ($tenantId === null) {
            // Tenant aniqlanmasa — hech kimni ko'rsatmaymiz (fail-closed).
            $query->whereRaw('1 = 0');

            return;
        }

        $root = Department::find($tenantId);
        $deptIds = $root !== null ? $root->descendantAndSelfIds() : [];
        // Organization modeli tenant-scoped — joriy tenant tashkilotlari.
        $orgIds = Organization::query()->pluck('id')->all();

        $query->where(fn (Builder $q) => $q
            ->whereIn('department_id', $deptIds)
            ->orWhereIn('organization_id', $orgIds));
    }

    /**
     * Tanlangan bo'lim actor tenantiga tegishli bo'lishi shart (cross-tenant admindan tashqari).
     */
    private function guardDepartmentTenant(User $actor, string $departmentId): void
    {
        if ($actor->hasRole('super-admin') || $actor->hasRole('viloyat-admin')) {
            return;
        }

        $dept = Department::find($departmentId);
        abort_if(
            $dept === null || $dept->rootId() !== $actor->hokimlik_id,
            403,
            'Бошқа ҳокимлик бўлимига фойдаланувчи бириктириб бўлмайди.',
        );
    }

    /**
     * Tayinlanayotgan rol actor darajasidan qat'iy past bo'lishi shart —
     * privilegiya eskalatsiyasining oldini oladi (masalan tuman-admin → super-admin).
     */
    private function guardAssignableRole(User $actor, string $roleName): void
    {
        abort_unless(
            RoleHierarchy::canAssign($actor, $roleName),
            403,
            'Бу лавозим/ролни тайинлашга рухсатингиз йўқ.',
        );
    }
}
