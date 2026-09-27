<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';

const props = defineProps<{
    tasks: {
        data: Array<Record<string, any>>;
        total: number;
    };
    isOrgUser: boolean;
    counts: Record<string, number>;
    filter: string;
}>();

const { success } = useFlash();

const reviewLabels: Record<string, string> = {
    submitted: 'Тасдиқ кутилмоқда', approved: 'Тасдиқланган', returned: 'Қайтарилган',
};
const reviewColors: Record<string, string> = {
    submitted: 'bg-amber-100 text-amber-800', approved: 'bg-green-100 text-green-700', returned: 'bg-orange-100 text-orange-700',
};

// Filtr kartalari (dashboard bilan bir xil) — bosilganda filtrlaydi
const filterCards = [
    { key: 'all', label: 'Жами топшириқ', color: 'text-slate-900', active: 'border-slate-400 ring-slate-200' },
    { key: 'under_control', label: 'Назоратда', color: 'text-emerald-600', active: 'border-emerald-400 ring-emerald-200' },
    { key: 'pending', label: 'Бажарилмаган', color: 'text-blue-600', active: 'border-blue-400 ring-blue-200' },
    { key: 'overdue', label: 'Муддати ўтган', color: 'text-red-600', active: 'border-red-400 ring-red-200' },
    { key: 'awaiting', label: 'Тасдиқ кутилмоқда', color: 'text-amber-600', active: 'border-amber-400 ring-amber-200' },
    { key: 'completed', label: 'Бажарилган', color: 'text-green-600', active: 'border-green-400 ring-green-200' },
];

function goFilter(key: string) {
    router.get('/topshiriqlar', key === 'all' ? {} : { f: key }, { preserveState: true, preserveScroll: true });
}

const statusLabels: Record<string, string> = {
    not_started: 'Бажарилмаган',
    in_progress: 'Бажарилмоқда',
    completed: 'Бажарилган',
    overdue: 'Муддати ўтган',
};
const statusColors: Record<string, string> = {
    not_started: 'bg-gray-100 text-gray-600',
    in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    overdue: 'bg-red-100 text-red-700',
};
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Топшириқлар</h1>
                <p v-if="isOrgUser" class="mt-1 text-sm text-gray-500">Ташкилотингизга келган топшириқлар</p>
            </div>
            <a v-if="!isOrgUser" href="/topshiriqlar/create"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги топшириқ
            </a>
        </div>

        <!-- KPI filtr картлари (босилганда филтрлайди) -->
        <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            <button v-for="c in filterCards" :key="c.key" @click="goFilter(c.key)"
                class="rounded-xl border bg-white p-4 text-left shadow-sm transition hover:shadow"
                :class="filter === c.key ? `ring-2 ${c.active}` : 'border-slate-200'">
                <p class="text-2xl font-bold" :class="c.color">{{ counts[c.key] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ c.label }}</p>
            </button>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Сарлавҳа</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Манба</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Масъул</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Муддат</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ҳолат</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Назорат</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="t in tasks.data" :key="t.id" class="cursor-pointer hover:bg-gray-50"
                        @click="router.get(`/topshiriqlar/${t.id}`)">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ t.title }}
                            <span v-if="t.documents_count" class="ml-1 text-[11px] text-gray-400">📎{{ t.documents_count }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded px-2 py-0.5 text-[11px] font-medium"
                                :class="t.source === 'control_plan' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'">
                                {{ t.source === 'control_plan' ? 'Назорат режа' : 'Мустақил' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ t.assignee ?? '—' }}
                            <span v-if="t.assignee_type === 'organization'" class="ml-1 rounded bg-violet-100 px-1.5 py-0.5 text-[10px] text-violet-700">ташкилот</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ t.deadline ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusColors[t.execution_status]">
                                {{ statusLabels[t.execution_status] ?? t.execution_status }}
                            </span>
                            <span v-if="t.review_status" class="ml-1 rounded-full px-2 py-0.5 text-[10px] font-medium" :class="reviewColors[t.review_status]">
                                {{ reviewLabels[t.review_status] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                                :class="t.under_control ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600'">
                                {{ t.under_control ? 'Назоратда' : 'Ечилган' }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="tasks.data.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Топшириқлар топилмади</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
