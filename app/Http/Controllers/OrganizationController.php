<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Department;
use App\Models\Organization;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function __construct(private TenantContext $context)
    {
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Organization::query()
            ->with(['kompleks:id,name_cyr', 'creator:id,name'])
            ->withCount('users')
            ->when($request->filled('search'), fn ($q) => $q->whereLike('name_cyr', '%'.$request->search.'%'))
            ->latest();

        // Kotibyat mudiri — faqat o'z kompleksi/o'zi yaratgan tashkilotlar
        if ($user->hasRole('kotibyat-mudiri') && ! $user->hasRole('tuman-admin')) {
            $query->where(function ($q) use ($user) {
                $q->where('kompleks_id', $user->department_id)
                    ->orWhere('created_by', $user->id);
            });
        }

        return Inertia::render('Organizations/Index', [
            'organizations' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Organizations/Create', [
            'komplekslar' => $this->komplekslar(),
        ]);
    }

    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['kompleks_id'] = $this->resolveKompleksId($request);
        // hokimlik_id — BelongsToTenant creating hook avtomatik to'ldiradi;
        // cross-tenant admin uchun joriy tenantni aniq beramiz.
        if ($this->context->id() !== null) {
            $data['hokimlik_id'] = $this->context->id();
        }

        $org = Organization::create($data);

        return redirect()
            ->route('organizations.show', $org->id)
            ->with('success', 'Ташкилот яратилди.');
    }

    public function show(Organization $organization): Response
    {
        $organization->load([
            'kompleks:id,name_cyr',
            'creator:id,name',
            'users:id,name,login,organization_id',
            'users.roles:id,name',
        ]);

        return Inertia::render('Organizations/Show', [
            'organization' => $organization,
            'canManageUsers' => auth()->user()->can('manageUsers', $organization),
        ]);
    }

    public function edit(Organization $organization): Response
    {
        return Inertia::render('Organizations/Edit', [
            'organization' => $organization,
            'komplekslar' => $this->komplekslar(),
        ]);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $data = $request->validated();
        // kompleks_id — faqat tenant ichidagi kompleksga ruxsat
        if (array_key_exists('kompleks_id', $data) && $data['kompleks_id'] !== null) {
            $data['kompleks_id'] = $this->validKompleksId((string) $data['kompleks_id']);
        }

        $organization->update($data);

        return redirect()
            ->route('organizations.show', $organization->id)
            ->with('success', 'Ташкилот маълумотлари янгиланди.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $organization->delete();

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Ташкилот ўчирилди.');
    }

    /**
     * Joriy tenant ichidagi komplekslar ro'yxati (form uchun).
     *
     * @return Collection<int, Department>
     */
    private function komplekslar()
    {
        return Department::query()
            ->komplekslar()
            ->when($this->context->id() !== null, fn ($q) => $q->where('parent_id', $this->context->id()))
            ->orderBy('sort_order')
            ->get(['id', 'name_cyr', 'parent_id']);
    }

    /** Store uchun kompleks_id ni aniqlash: kotibyat mudirining bo'limi yoki so'rovdan. */
    private function resolveKompleksId(Request $request): ?string
    {
        $user = $request->user();

        // Kotibyat mudirining department_id'si kompleks bo'lsa — o'shani ishlatamiz
        if ($user->department && $user->department->type === 'kompleks') {
            return $user->department_id;
        }

        $requested = $request->input('kompleks_id');

        return $requested !== null ? $this->validKompleksId((string) $requested) : null;
    }

    /** kompleks_id joriy tenant ichidagi haqiqiy kompleks ekanini tekshiradi. */
    private function validKompleksId(string $kompleksId): ?string
    {
        $valid = Department::query()
            ->komplekslar()
            ->when($this->context->id() !== null, fn ($q) => $q->where('parent_id', $this->context->id()))
            ->whereKey($kompleksId)
            ->exists();

        return $valid ? $kompleksId : null;
    }
}
