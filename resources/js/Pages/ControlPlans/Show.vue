<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { useFormatters } from '@/Composables/useFormatters';
import {
    EXECUTION_STATUS_LABELS as execLabels,
    EXECUTION_STATUS_TEXT_COLORS as execColors,
} from '@/Constants/labels';
import type { ControlPlan, ControlPlanItem } from '@/types';

const props = defineProps<{ plan: ControlPlan; isCreator: boolean }>();
const { success } = useFlash();
const { formatDateUz: formatDate, formatSize } = useFormatters();

const expandedItem = ref<number | null>(null);
const uploadingItem = ref<number | null>(null);
const uploadFile = ref<File | null>(null);
const uploadDesc = ref('');

const stats = computed(() => {
    const items = props.plan.items ?? [];
    return {
        total: items.length,
        not_started: items.filter((i: ControlPlanItem) => i.execution_status === 'not_started').length,
        in_progress: items.filter((i: ControlPlanItem) => i.execution_status === 'in_progress').length,
        completed: items.filter((i: ControlPlanItem) => i.execution_status === 'completed').length,
        overdue: items.filter((i: ControlPlanItem) => i.execution_status === 'overdue').length,
    };
});

function toggleItem(id: number) {
    expandedItem.value = expandedItem.value === id ? null : id;
}

function startUpload(itemId: number) {
    uploadingItem.value = itemId;
    uploadFile.value = null;
    uploadDesc.value = '';
}

function onFileSelect(e: Event) {
    uploadFile.value = (e.target as HTMLInputElement).files?.[0] ?? null;
}

function submitUpload() {
    if (!uploadFile.value || !uploadingItem.value) return;
    const formData = new FormData();
    formData.append('file', uploadFile.value);
    if (uploadDesc.value) formData.append('description', uploadDesc.value);
    router.post(`/control-plans/items/${uploadingItem.value}/documents`, formData, {
        forceFormData: true,
        onSuccess: () => { uploadingItem.value = null; },
    });
}

function deleteDoc(docId: number) {
    if (confirm('Ҳужжатни ўчиришга ишончингиз комилми?')) {
        router.delete(`/documents/${docId}`);
    }
}

const activeFilters = ref<Set<string>>(new Set());

const filteredItems = computed<ControlPlanItem[]>(() => {
    const items = props.plan.items ?? [];
    if (activeFilters.value.size === 0) return items;
    return items.filter((i: ControlPlanItem) => activeFilters.value.has(i.execution_status));
});

function toggleFilter(status: string) {
    const s = new Set(activeFilters.value);
    if (s.has(status)) {
        s.delete(status);
    } else {
        s.add(status);
    }
    activeFilters.value = s;
}

function clearFilters() {
    activeFilters.value = new Set();
}

function saveItemStatus(itemId: number, status: string, report: string) {
    router.put(`/control-plans/items/${itemId}/status`, {
        execution_status: status,
        execution_report: report,
    }, {
        preserveScroll: true,
    });
}

function deletePlan() {
    if (confirm('Назорат режани ўчиришга ишончингиз комилми?')) {
        router.delete(`/control-plans/${props.plan.id}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <!-- Сарлавҳа -->
        <div class="mb-4 flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span v-if="plan.document_number" class="text-lg font-bold text-blue-700">{{ plan.document_number }}</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="plan.status === 'active' ? 'bg-green-100 text-green-700' : plan.status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'">
                        {{ plan.status === 'active' ? 'Фаол' : plan.status === 'completed' ? 'Бажарилган' : 'Архив' }}
                    </span>
                </div>
                <p v-if="plan.status_date" class="text-sm font-semibold italic text-red-600">{{ plan.status_date }}</p>
            </div>
            <div class="flex gap-2">
                <a :href="`/control-plans/${plan.id}/export`"
                    class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM13 3.5L18.5 9H14a1 1 0 0 1-1-1V3.5zM7 17l1.5-5L10 17h1l2-7h-1.2l-1.3 5L9 10H8l-1.5 5L5.2 10H4l2 7h1z"/>
                    </svg>
                    Word файлга юклаш
                </a>
                <Link v-if="isCreator" :href="`/control-plans/${plan.id}/edit`"
                    class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    Таҳрирлаш
                </Link>
                <button v-if="isCreator" @click="deletePlan"
                    class="inline-flex items-center gap-2 rounded-md border border-red-300 bg-white px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Ўчириш
                </button>
            </div>
        </div>

        <!-- Расмий сарлавҳа -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow text-center">
            <p class="text-sm font-semibold text-gray-900 leading-relaxed">{{ plan.title }}</p>
        </div>

        <!-- Статистика (фильтр) -->
        <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-5">
            <div @click="clearFilters" class="cursor-pointer rounded-lg p-3 shadow text-center transition"
                :class="activeFilters.size === 0 ? 'bg-gray-800 text-white ring-2 ring-gray-800' : 'bg-white hover:bg-gray-50'">
                <p class="text-2xl font-bold">{{ stats.total }}</p>
                <p class="mt-1 text-xs" :class="activeFilters.size === 0 ? 'text-gray-300' : 'text-gray-500'">Жами топшириқлар</p>
            </div>
            <div @click="toggleFilter('not_started')" class="cursor-pointer rounded-lg p-3 shadow text-center border-l-4 border-gray-300 transition"
                :class="activeFilters.has('not_started') ? 'bg-gray-100 ring-2 ring-gray-400' : 'bg-white hover:bg-gray-50'">
                <p class="text-2xl font-bold text-gray-600">{{ stats.not_started }}</p>
                <p class="mt-1 text-xs text-gray-500">Бажарилмаган</p>
            </div>
            <div @click="toggleFilter('in_progress')" class="cursor-pointer rounded-lg p-3 shadow text-center border-l-4 border-blue-500 transition"
                :class="activeFilters.has('in_progress') ? 'bg-blue-50 ring-2 ring-blue-400' : 'bg-white hover:bg-gray-50'">
                <p class="text-2xl font-bold text-blue-600">{{ stats.in_progress }}</p>
                <p class="mt-1 text-xs text-gray-500">Бажарилмоқда</p>
            </div>
            <div @click="toggleFilter('completed')" class="cursor-pointer rounded-lg p-3 shadow text-center border-l-4 border-green-500 transition"
                :class="activeFilters.has('completed') ? 'bg-green-50 ring-2 ring-green-400' : 'bg-white hover:bg-gray-50'">
                <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
                <p class="mt-1 text-xs text-gray-500">Бажарилган</p>
            </div>
            <div @click="toggleFilter('overdue')" class="cursor-pointer rounded-lg p-3 shadow text-center border-l-4 border-red-500 transition"
                :class="activeFilters.has('overdue') ? 'bg-red-50 ring-2 ring-red-400' : 'bg-white hover:bg-gray-50'">
                <p class="text-2xl font-bold text-red-600">{{ stats.overdue }}</p>
                <p class="mt-1 text-xs text-gray-500">Муддати ўтган</p>
            </div>
        </div>

        <!-- Word жадвали -->
        <div class="rounded-lg bg-white shadow overflow-x-auto">
            <table class="w-full border-collapse border border-gray-400 text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-3 py-2 text-center font-semibold w-12">Т/р</th>
                        <th class="border border-gray-400 px-3 py-2 text-center font-semibold" style="min-width:250px">Чора-тадбирлар номи</th>
                        <th class="border border-gray-400 px-3 py-2 text-center font-semibold" style="min-width:200px">Амалга оширилиш механизми</th>
                        <th class="border border-gray-400 px-3 py-2 text-center font-semibold" style="min-width:120px">Молиялаштириш манбаси</th>
                        <th class="border border-gray-400 px-3 py-2 text-center font-semibold w-28">Ижро муддати</th>
                        <th class="border border-gray-400 px-3 py-2 text-center font-semibold" style="min-width:150px">Масъуллар</th>
                        <th class="border border-gray-400 px-3 py-2 text-center font-semibold" style="min-width:200px">Бажарилиши</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in filteredItems" :key="item.id" class="align-top hover:bg-blue-50/30 cursor-pointer" @click="router.get(`/control-plans/items/${item.id}`)">
                        <td class="border border-gray-400 px-3 py-2 text-center font-semibold">{{ item.item_number }}.</td>
                        <td class="border border-gray-400 px-3 py-2">{{ item.task_description || '—' }}</td>
                        <td class="border border-gray-400 px-3 py-2 whitespace-pre-wrap">{{ item.implementation || '—' }}</td>
                        <td class="border border-gray-400 px-3 py-2 text-center">{{ item.funding_source || '—' }}</td>
                        <td class="border border-gray-400 px-3 py-2 text-center">{{ item.deadline ? formatDate(item.deadline) : '—' }}</td>
                        <td class="border border-gray-400 px-3 py-2 text-center">
                            <div v-for="r in item.responsibles" :key="r.id" class="mb-1">
                                <span v-if="r.display_position" class="text-xs text-gray-600 block">{{ r.display_position }}</span>
                                <span class="font-medium" :class="r.is_primary ? 'text-amber-700' : ''">
                                    <span v-if="r.is_primary" title="Асосий ижрочи" class="text-amber-500">★</span>
                                    {{ r.responsible_name }}
                                </span>
                            </div>
                            <span v-if="!item.responsibles?.length" class="text-gray-400">—</span>
                        </td>
                        <td class="border border-gray-400 px-3 py-2">
                            <span :class="execColors[item.execution_status]">{{ execLabels[item.execution_status] }}.</span>
                            <p v-if="item.execution_report" class="mt-1 text-gray-700 whitespace-pre-wrap">{{ item.execution_report }}</p>
                        </td>
                    </tr>
                    <tr v-if="filteredItems.length === 0">
                        <td colspan="7" class="border border-gray-400 px-4 py-8 text-center text-gray-500">
                            {{ activeFilters.size > 0 ? 'Ушбу ҳолатдаги топшириқлар мавжуд эмас' : 'Топшириқлар мавжуд эмас' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-xs text-gray-400 text-center">Банд устига босиб тафсилотларга ўтинг</p>
    </AppLayout>
</template>
