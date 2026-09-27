<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ControlPlan;
use App\Models\ControlPlanItem;
use App\Models\Department;
use App\Models\User;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $context = app(TenantContext::class);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'roles' => fn () => $request->user()?->getRoleNames() ?? [],
                'permissions' => fn () => $request->user()?->getAllPermissions()->pluck('name') ?? [],
                // Ташкилот фойдаланувчиси учун ўз ташкилоти
                'organization' => fn () => $request->user()?->organization_id
                    ? $request->user()->organization()->first(['id', 'name_cyr'])
                    : null,
            ],
            'tenant' => fn () => $this->buildTenantPayload($request, $context),
            'notifications' => fn () => $this->notificationCounts($request),
            'navCounts' => fn () => $this->navCounts($request->user()),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Bildirishnoma: tasdiqlash kutilayotgan ijrolar (son + qisqa ro'yxat).
     *
     * @return array<string, mixed>
     */
    private function notificationCounts(Request $request): array
    {
        $user = $request->user();

        // Faqat ichki nazorat qiluvchilar (kotibyat mudiri / topshiriq biriktiruvchilar)
        if (! $user || $user->organization_id !== null || ! $user->can('topshiriqlar.assign-org')) {
            return ['pending_approvals' => 0, 'pending_list' => []];
        }

        $base = ControlPlanItem::query()
            ->whereNull('control_removed_at')
            ->where('review_status', 'submitted')
            ->where(fn ($q) => $q->where('created_by', $user->id)->orWhere('kompleks_id', $user->department_id));

        $list = (clone $base)->with('responsibles')->latest('submitted_at')->take(5)->get()
            ->map(fn (ControlPlanItem $t) => [
                'id' => $t->id,
                'title' => $t->title ?? $t->task_description,
                'assignee' => ($t->responsibles->firstWhere('is_primary', true) ?? $t->responsibles->first())?->displayName(),
                'submitted_at' => $t->submitted_at?->format('d.m.Y H:i'),
            ])->all();

        return [
            'pending_approvals' => (clone $base)->count(),
            'pending_list' => $list,
        ];
    }

    /**
     * Sidebar nav uchun sonlar: faol rejalar + bajarilmagan topshiriqlar.
     *
     * @return array<string, int>
     */
    private function navCounts(?User $user): array
    {
        if (! $user) {
            return ['plans' => 0, 'tasks' => 0];
        }

        // Topshiriqlar: bajarilmagan (pending) son — foydalanuvchi ko'lamiga qarab
        $taskQ = ControlPlanItem::query()
            ->whereNull('control_removed_at')
            ->whereIn('execution_status', ['not_started', 'in_progress']);

        if ($user->organization_id !== null) {
            $taskQ->whereHas('responsibles', fn ($r) => $r
                ->where('assignee_type', 'organization')->where('assignee_id', $user->organization_id));
        } elseif ($user->hasRole('kotibyat-mudiri') && ! $user->hasRole('tuman-admin')) {
            $taskQ->where('source', 'standalone')
                ->where(fn ($w) => $w->where('created_by', $user->id)->orWhere('kompleks_id', $user->department_id));
        } else {
            $taskQ->where('source', 'standalone');
        }

        return [
            'plans' => $user->can('tadbirlar.view') ? ControlPlan::where('status', 'active')->count() : 0,
            'tasks' => $taskQ->count(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildTenantPayload(Request $request, TenantContext $context): ?array
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        // Joriy tenant (super-admin uchun null bo'lishi mumkin)
        $current = null;
        if ($context->id() !== null) {
            $current = Department::query()
                ->whereNull('parent_id')
                ->find($context->id(), ['id', 'name_cyr', 'name_lat', 'type']);
        }

        // Super-admin / viloyat-admin uchun tenant switcher ro'yxati
        $available = null;
        if ($context->isGlobal() || $user->hasRole('super-admin') || $user->hasRole('viloyat-admin')) {
            $available = Department::query()
                ->tenants()
                ->orderBy('sort_order')
                ->get(['id', 'name_cyr', 'type']);
        }

        return [
            'current' => $current,
            'is_global' => $context->isGlobal(),
            'is_cross_tenant' => $user->hasRole('super-admin') || $user->hasRole('viloyat-admin'),
            'available' => $available,
        ];
    }
}
