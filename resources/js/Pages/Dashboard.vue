<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import DonutChart from '@/Components/UI/DonutChart.vue';
import TaskCalendar from '@/Components/UI/TaskCalendar.vue';
import { Link, router } from '@inertiajs/vue3';

interface TenantStat {
    id: number;
    name_cyr: string;
    type: string;
    employees: number;
    control_plans: number;
    overdue_items: number;
}

interface ActivityItem {
    id: number;
    description: string;
    subject_type: string;
    causer: string;
    created_at: string;
}

interface PerTenantStats {
    total_employees: number;
    archived_employees: number;
    control_plans: number;
    active_plans: number;
}

interface ControlPlanStats {
    total_items: number;
    completed: number;
    in_progress: number;
    overdue: number;
}

interface DashTask {
    id: number; title: string; assignee?: string; deadline?: string;
    days_left?: number | null; status: string; source: string; submitted_at?: string;
}
interface CalTask { id: number; title: string; date: string; status: string }
interface OrgRow { id: number; name: string; total: number; completed: number; pending: number; overdue: number }
interface KotibyatKpi {
    tasks_total: number; under_control: number; completed: number;
    overdue: number; pending: number; awaiting: number; plans_active: number; organizations: number;
}
interface OrgStats {
    name?: string;
    kpi: { total: number; pending: number; completed: number; overdue: number };
    due_soon: DashTask[];
    calendar_tasks?: CalTask[];
}

defineProps<{
    mode: 'cross-tenant' | 'tenant' | 'kotibyat' | 'organization';
    tenants_stats?: TenantStat[];
    stats?: PerTenantStats;
    control_plan_stats?: ControlPlanStats;
    kpi?: KotibyatKpi;
    status_chart?: { label: string; value: number; color: string }[];
    awaiting_approval?: DashTask[];
    due_soon?: DashTask[];
    pending_tasks?: DashTask[];
    by_organization?: OrgRow[];
    calendar_tasks?: CalTask[];
    org?: OrgStats;
    recent_activity?: ActivityItem[];
}>();

function viewTenant(tenantId: number) {
    router.get('/', { tenant: tenantId });
}

/** Filtrlangan topshiriqlar sahifasiga o'tish. */
function goTasks(f: string) {
    router.get('/topshiriqlar', f === 'all' ? {} : { f });
}

const statusLabels: Record<string, string> = {
    not_started: 'Бажарилмаган', in_progress: 'Бажарилмоқда', completed: 'Бажарилган', overdue: 'Муддати ўтган',
};
const statusColors: Record<string, string> = {
    not_started: 'bg-gray-100 text-gray-600', in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700', overdue: 'bg-red-100 text-red-700',
};

/** Foiz (stacked bar uchun). */
function pct(value: number, total: number): number {
    return total > 0 ? Math.round((value / total) * 100) : 0;
}

/** Muddatgacha qolgan kunlar — rang + matn. */
function deadlineBadge(days: number | null | undefined): { text: string; cls: string } {
    if (days === null || days === undefined) return { text: '—', cls: 'bg-gray-100 text-gray-500' };
    if (days < 0) return { text: `${Math.abs(days)} кун кеч`, cls: 'bg-red-100 text-red-700' };
    if (days === 0) return { text: 'Бугун', cls: 'bg-orange-100 text-orange-700' };
    if (days <= 3) return { text: `${days} кун қолди`, cls: 'bg-orange-100 text-orange-700' };
    return { text: `${days} кун қолди`, cls: 'bg-amber-100 text-amber-700' };
}
</script>

<template>
    <AppLayout>
        <!-- Сарлавҳа -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Бош саҳифа</h1>
            <p v-if="mode === 'cross-tenant'" class="mt-1 text-sm text-slate-500">
                Барча ҳокимликлар бўйича умумий статистика
            </p>
            <p v-else-if="mode === 'kotibyat'" class="mt-1 text-sm text-slate-500">
                Котибият мудири панели — топшириқлар ва ташкилотлар назорати
            </p>
            <p v-else-if="mode === 'organization'" class="mt-1 text-sm text-slate-500">
                {{ org?.name }} — сизга келган топшириқлар
            </p>
            <p v-else class="mt-1 text-sm text-slate-500">
                Жорий ҳокимлик бўйича статистика
            </p>
        </div>

        <!-- ===== CROSS-TENANT REJIMI ===== -->
        <div v-if="mode === 'cross-tenant' && tenants_stats">
            <!-- Tumanlar grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <button v-for="t in tenants_stats" :key="t.id" @click="viewTenant(t.id)"
                    class="group rounded-xl border border-slate-200 bg-white p-5 text-left shadow-sm transition-all hover:border-blue-300 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg"
                            :class="t.type === 'viloyat' ? 'bg-blue-100' : t.type === 'shahar' ? 'bg-emerald-100' : 'bg-amber-100'">
                            <svg class="h-5 w-5"
                                :class="t.type === 'viloyat' ? 'text-blue-600' : t.type === 'shahar' ? 'text-emerald-600' : 'text-amber-600'"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                        </div>
                        <span v-if="t.overdue_items > 0"
                            class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-600 animate-pulse"></span>
                            {{ t.overdue_items }} муддати ўтган
                        </span>
                    </div>
                    <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-blue-700">{{ t.name_cyr }}</h3>
                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ t.employees }}</p>
                            <p class="text-xs text-slate-500">Ходимлар</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ t.control_plans }}</p>
                            <p class="text-xs text-slate-500">Назорат режа</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- ===== PER-TENANT REJIMI ===== -->
        <div v-else-if="mode === 'tenant' && stats">
            <!-- Stat kartochkalari -->
            <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ stats.total_employees }}</p>
                    <p class="mt-1 text-sm text-slate-500">Жами ходимлар</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ stats.archived_employees }}</p>
                    <p class="mt-1 text-sm text-slate-500">Архивдаги</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100">
                        <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ stats.control_plans }}</p>
                    <p class="mt-1 text-sm text-slate-500">Жами назорат режа</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ stats.active_plans }}</p>
                    <p class="mt-1 text-sm text-slate-500">Фаол режа</p>
                </div>
            </div>

            <!-- Control plan items statistics -->
            <div v-if="control_plan_stats" class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-white p-4 shadow text-center border-l-4 border-gray-400">
                    <p class="text-2xl font-bold">{{ control_plan_stats.total_items }}</p>
                    <p class="text-xs text-gray-500">Жами топшириқлар</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow text-center border-l-4 border-blue-500">
                    <p class="text-2xl font-bold text-blue-600">{{ control_plan_stats.in_progress }}</p>
                    <p class="text-xs text-gray-500">Бажарилмоқда</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow text-center border-l-4 border-green-500">
                    <p class="text-2xl font-bold text-green-600">{{ control_plan_stats.completed }}</p>
                    <p class="text-xs text-gray-500">Бажарилган</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow text-center border-l-4 border-red-500">
                    <p class="text-2xl font-bold text-red-600">{{ control_plan_stats.overdue }}</p>
                    <p class="text-xs text-gray-500">Муддати ўтган</p>
                </div>
            </div>
        </div>

        <!-- ===== КОТИБИЯТ МУДИРИ ПАНЕЛИ ===== -->
        <div v-else-if="mode === 'kotibyat' && kpi">
            <!-- KPI картлари (босилганда филтрлайди) -->
            <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-7">
                <button @click="goTasks('all')" class="rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-slate-200">
                    <p class="text-3xl font-bold text-slate-900">{{ kpi.tasks_total }}</p>
                    <p class="mt-1 text-xs text-slate-500">Жами топшириқ</p>
                </button>
                <button @click="goTasks('under_control')" class="rounded-xl border border-emerald-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-emerald-200">
                    <p class="text-3xl font-bold text-emerald-600">{{ kpi.under_control }}</p>
                    <p class="mt-1 text-xs text-slate-500">Назоратда</p>
                </button>
                <button @click="goTasks('pending')" class="rounded-xl border border-blue-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-blue-200">
                    <p class="text-3xl font-bold text-blue-600">{{ kpi.pending }}</p>
                    <p class="mt-1 text-xs text-slate-500">Бажарилмаган</p>
                </button>
                <button @click="goTasks('overdue')" class="rounded-xl border border-red-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-red-200">
                    <p class="text-3xl font-bold text-red-600">{{ kpi.overdue }}</p>
                    <p class="mt-1 text-xs text-slate-500">Муддати ўтган</p>
                </button>
                <button @click="goTasks('awaiting')" class="rounded-xl border-2 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-amber-200" :class="kpi.awaiting ? 'border-amber-300' : 'border-slate-200'">
                    <p class="text-3xl font-bold" :class="kpi.awaiting ? 'text-amber-600' : 'text-slate-900'">{{ kpi.awaiting }}</p>
                    <p class="mt-1 text-xs text-slate-500">Тасдиқ кутилмоқда</p>
                </button>
                <button @click="router.get('/control-plans')" class="rounded-xl border border-violet-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-violet-200">
                    <p class="text-3xl font-bold text-violet-600">{{ kpi.plans_active }}</p>
                    <p class="mt-1 text-xs text-slate-500">Фаол режа</p>
                </button>
                <button @click="router.get('/organizations')" class="rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-slate-200">
                    <p class="text-3xl font-bold text-slate-900">{{ kpi.organizations }}</p>
                    <p class="mt-1 text-xs text-slate-500">Ташкилотлар</p>
                </button>
            </div>

            <!-- ТАСДИҚЛАШ КУТИЛМОҚДА (EDO inbox) -->
            <div v-if="(awaiting_approval ?? []).length" class="mb-6 overflow-hidden rounded-xl border-2 border-amber-300 bg-amber-50/40 shadow-sm">
                <div class="border-b border-amber-200 px-5 py-4">
                    <h2 class="font-semibold text-amber-900">⏳ Тасдиқлаш кутилмоқда</h2>
                    <p class="text-xs text-amber-600">Ташкилотлар ижрони якунлаб, тасдиқлашга юборган топшириқлар</p>
                </div>
                <div class="divide-y divide-amber-100">
                    <div v-for="t in awaiting_approval" :key="t.id" @click="router.get(`/topshiriqlar/${t.id}`)"
                        class="flex cursor-pointer items-center gap-3 px-5 py-3 hover:bg-amber-50">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-base">📥</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ t.title }}</p>
                            <p class="truncate text-xs text-slate-500">{{ t.assignee ?? '—' }} · юборилди: {{ t.submitted_at }}</p>
                        </div>
                        <span class="shrink-0 rounded-md bg-amber-600 px-3 py-1 text-xs font-semibold text-white">Кўриш →</span>
                    </div>
                </div>
            </div>

            <!-- Қатор B: Ҳолат диаграммаси + Муддати яқин -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Donut: topshiriqlar holati -->
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-semibold text-slate-900">📊 Топшириқлар ҳолати</h2>
                        <p class="text-xs text-slate-400">Ижро бўйича тақсимот</p>
                    </div>
                    <div class="flex min-h-[200px] items-center justify-center p-6">
                        <DonutChart v-if="status_chart && (kpi.tasks_total > 0)" :segments="status_chart" />
                        <p v-else class="text-sm text-slate-400">Маълумот йўқ</p>
                    </div>
                </div>

                <!-- Muddati yaqin topshiriqlar -->
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-semibold text-slate-900">⏰ Муддати яқин топшириқлар</h2>
                        <p class="text-xs text-slate-400">7 кун ичида ижро муддати тугайдиганлар</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="t in (due_soon ?? [])" :key="t.id" @click="router.get(`/topshiriqlar/${t.id}`)"
                            class="flex cursor-pointer items-center gap-3 px-5 py-3 hover:bg-slate-50">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ t.title }}</p>
                                <p class="truncate text-xs text-slate-400">{{ t.assignee ?? '—' }} · {{ t.deadline }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium" :class="deadlineBadge(t.days_left).cls">
                                {{ deadlineBadge(t.days_left).text }}
                            </span>
                        </div>
                        <p v-if="!(due_soon ?? []).length" class="px-5 py-8 text-center text-sm text-slate-400">Муддати яқин топшириқ йўқ</p>
                    </div>
                </div>
            </div>

            <!-- Топшириқлар тақвими -->
            <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <TaskCalendar :tasks="calendar_tasks ?? []" />
            </div>

            <!-- Қатор C: Ташкилотлар графиги + Бажарилмаган -->
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Tashkilotlar kesimi (stacked bar graf) -->
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div>
                            <h2 class="font-semibold text-slate-900">🏢 Ташкилотлар кесими</h2>
                            <p class="text-xs text-slate-400">Ижро ҳолати бўйича</p>
                        </div>
                        <div class="flex gap-3 text-[11px] text-slate-500">
                            <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-green-500"></span>Бажарилган</span>
                            <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-blue-500"></span>Жараёнда</span>
                            <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-red-500"></span>Муддати ўтган</span>
                        </div>
                    </div>
                    <div class="space-y-4 p-5">
                        <div v-for="o in (by_organization ?? [])" :key="o.id" @click="router.get(`/organizations/${o.id}`)"
                            class="cursor-pointer">
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="truncate font-medium text-slate-800">{{ o.name }}</span>
                                <span class="shrink-0 text-xs text-slate-400">{{ o.total }} топшириқ</span>
                            </div>
                            <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="bg-green-500" :style="{ width: pct(o.completed, o.total) + '%' }"></div>
                                <div class="bg-blue-500" :style="{ width: pct(o.pending - o.overdue, o.total) + '%' }"></div>
                                <div class="bg-red-500" :style="{ width: pct(o.overdue, o.total) + '%' }"></div>
                            </div>
                            <div class="mt-1 flex gap-3 text-[11px] text-slate-400">
                                <span>✓ {{ o.completed }}</span>
                                <span>● {{ Math.max(0, o.pending - o.overdue) }}</span>
                                <span :class="o.overdue ? 'text-red-500' : ''">⚠ {{ o.overdue }}</span>
                            </div>
                        </div>
                        <p v-if="!(by_organization ?? []).length" class="py-6 text-center text-sm text-slate-400">Ташкилот қўшилмаган</p>
                    </div>
                </div>

                <!-- Bajarilmagan topshiriqlar -->
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-semibold text-slate-900">📋 Бажарилмаган топшириқлар</h2>
                        <p class="text-xs text-slate-400">Ҳали ижро этилмаган топшириқлар</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="t in (pending_tasks ?? [])" :key="t.id" @click="router.get(`/topshiriqlar/${t.id}`)"
                            class="flex cursor-pointer items-center gap-3 px-5 py-3 hover:bg-slate-50">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ t.title }}</p>
                                <p class="truncate text-xs text-slate-400">{{ t.assignee ?? '—' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium" :class="statusColors[t.status]">
                                {{ statusLabels[t.status] ?? t.status }}
                            </span>
                        </div>
                        <p v-if="!(pending_tasks ?? []).length" class="px-5 py-8 text-center text-sm text-slate-400">Барча топшириқлар ижро этилган 🎉</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== ТАШКИЛОТ ПАНЕЛИ ===== -->
        <div v-else-if="mode === 'organization' && org">
            <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                <button @click="goTasks('all')" class="rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-slate-200">
                    <p class="text-3xl font-bold text-slate-900">{{ org.kpi.total }}</p>
                    <p class="mt-1 text-xs text-slate-500">Жами топшириқ</p>
                </button>
                <button @click="goTasks('pending')" class="rounded-xl border border-blue-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-blue-200">
                    <p class="text-3xl font-bold text-blue-600">{{ org.kpi.pending }}</p>
                    <p class="mt-1 text-xs text-slate-500">Бажарилмаган</p>
                </button>
                <button @click="goTasks('completed')" class="rounded-xl border border-green-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-green-200">
                    <p class="text-3xl font-bold text-green-600">{{ org.kpi.completed }}</p>
                    <p class="mt-1 text-xs text-slate-500">Бажарилган</p>
                </button>
                <button @click="goTasks('overdue')" class="rounded-xl border border-red-200 bg-white p-4 text-left shadow-sm transition hover:shadow hover:ring-2 hover:ring-red-200">
                    <p class="text-3xl font-bold text-red-600">{{ org.kpi.overdue }}</p>
                    <p class="mt-1 text-xs text-slate-500">Муддати ўтган</p>
                </button>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">⏰ Муддати яқин топшириқлар</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <div v-for="t in (org.due_soon ?? [])" :key="t.id" @click="router.get(`/topshiriqlar/${t.id}`)"
                        class="flex cursor-pointer items-center gap-3 px-5 py-3 hover:bg-slate-50">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ t.title }}</p>
                            <p class="truncate text-xs text-slate-400">{{ t.deadline }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium" :class="deadlineBadge(t.days_left).cls">
                            {{ deadlineBadge(t.days_left).text }}
                        </span>
                    </div>
                    <p v-if="!(org.due_soon ?? []).length" class="px-5 py-8 text-center text-sm text-slate-400">Муддати яқин топшириқ йўқ</p>
                </div>
            </div>

            <!-- Тақвим -->
            <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <TaskCalendar :tasks="org.calendar_tasks ?? []" />
            </div>
        </div>

        <!-- Oxirgi o'zgarishlar -->
        <div v-if="recent_activity" class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Охирги ўзгаришлар</h2>
                    <p class="text-xs text-slate-400">Тизимдаги сўнгги амаллар</p>
                </div>
                <Link href="/audit"
                    class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                    Барчасини кўриш
                </Link>
            </div>
            <div class="divide-y divide-slate-100">
                <div v-for="item in recent_activity" :key="item.id"
                    class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100">
                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-900">{{ item.description }}</span>
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500">{{ item.subject_type }}</span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-400">{{ item.causer }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-slate-400">{{ item.created_at }}</span>
                </div>
                <div v-if="recent_activity.length === 0" class="flex flex-col items-center py-12 text-slate-400">
                    <p class="text-sm">Ҳали ўзгаришлар мавжуд эмас</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
