<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { useFormatters } from '@/Composables/useFormatters';
import {
    PLAN_STATUS_LABELS as statusLabels,
    PLAN_STATUS_COLORS as statusColors,
    DEBOUNCE_DELAY,
} from '@/Constants/labels';
import type { PaginatedResponse, ControlPlan } from '@/types';

const props = defineProps<{
    plans: PaginatedResponse<ControlPlan>;
    filters: Record<string, string | undefined>;
}>();

const { success } = useFlash();
const { formatDateShort: formatDate } = useFormatters();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? null);

let timer: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/control-plans', {
            search: search.value || undefined,
            status: status.value || undefined,
        }, { preserveState: true, preserveScroll: true });
    }, DEBOUNCE_DELAY);
}

watch(search, applyFilters);
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Назорат режалар</h1>
            <Link href="/control-plans/create" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги назорат режа
            </Link>
        </div>

        <div class="mb-4 flex gap-3">
            <div class="flex-1">
                <input v-model="search" type="text" placeholder="Сарлавҳа ёки ҳужжат рақами бўйича..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </div>
            <select v-model="status" @change="applyFilters" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                <option :value="null">Барча ҳолатлар</option>
                <option value="active">Фаол</option>
                <option value="completed">Бажарилган</option>
                <option value="archived">Архив</option>
            </select>
        </div>

        <div class="space-y-3">
            <Link v-for="plan in plans.data" :key="plan.id" :href="`/control-plans/${plan.id}`"
                class="block rounded-lg bg-white p-4 shadow transition hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span v-if="plan.document_number" class="text-sm font-bold text-blue-600">{{ plan.document_number }}</span>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusColors[plan.status]">
                                {{ statusLabels[plan.status] }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-900 line-clamp-2">{{ plan.title }}</p>
                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                            <span>{{ plan.items_count }} та банд</span>
                            <span v-if="plan.document_date">Ҳужжат: {{ formatDate(plan.document_date) }}</span>
                            <span v-if="plan.status_date">{{ plan.status_date }}</span>
                            <span>Яратган: {{ plan.creator?.name ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </Link>

            <div v-if="plans.data.length === 0" class="rounded-lg bg-white p-8 text-center text-sm text-gray-500 shadow">
                Назорат режалар топилмади
            </div>
        </div>
    </AppLayout>
</template>
