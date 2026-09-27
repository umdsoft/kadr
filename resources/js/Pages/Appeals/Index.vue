<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { useFormatters } from '@/Composables/useFormatters';
import {
    APPEAL_STATUS_LABELS, APPEAL_STATUS_COLORS,
    APPEAL_PRIORITY_LABELS, APPEAL_PRIORITY_COLORS,
    DEBOUNCE_DELAY,
} from '@/Constants/labels';
import type { PaginatedResponse, CitizenAppeal, AppealCategory } from '@/types';

const props = defineProps<{
    appeals: PaginatedResponse<CitizenAppeal>;
    filters: Record<string, string | number | undefined>;
    categories: AppealCategory[];
}>();

const { success } = useFlash();
const { formatDateShort } = useFormatters();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? null);
const priority = ref(props.filters.priority ?? null);
const categoryId = ref(props.filters.category_id ?? null);
const showFilters = ref(false);

let timer: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/appeals', {
            search: search.value || undefined,
            status: status.value || undefined,
            priority: priority.value || undefined,
            category_id: categoryId.value || undefined,
        }, { preserveState: true, preserveScroll: true });
    }, DEBOUNCE_DELAY);
}

watch(search, applyFilters);

const hasFilters = computed(() => status.value || priority.value || categoryId.value);

function clearFilters() {
    search.value = '';
    status.value = null;
    priority.value = null;
    categoryId.value = null;
    applyFilters();
}

function isOverdue(appeal: CitizenAppeal): boolean {
    if (!appeal.sla_due_at) return false;
    if (['completed', 'closed'].includes(appeal.status)) return false;
    return new Date(appeal.sla_due_at) < new Date();
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Мурожаатлар</h1>
                <p class="text-sm text-gray-500">Жами: {{ appeals.total }}</p>
            </div>
            <Link href="/appeals/create"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги мурожаат
            </Link>
        </div>

        <div class="mb-4 flex gap-3">
            <input v-model="search" type="text" placeholder="Мурожаатчи ёки матн бўйича қидириш..."
                class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm" />
            <button @click="showFilters = !showFilters"
                class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm"
                :class="hasFilters ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 bg-white text-gray-700'">
                Фильтр
                <span v-if="hasFilters" class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs text-white">!</span>
            </button>
        </div>

        <div v-if="showFilters" class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Ҳолат</label>
                    <select v-model="status" @change="applyFilters" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">Барчаси</option>
                        <option v-for="(label, key) in APPEAL_STATUS_LABELS" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Муҳимлиги</label>
                    <select v-model="priority" @change="applyFilters" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">Барчаси</option>
                        <option v-for="(label, key) in APPEAL_PRIORITY_LABELS" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Категория</label>
                    <select v-model="categoryId" @change="applyFilters" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">Барчаси</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_cyr }}</option>
                    </select>
                </div>
            </div>
            <div v-if="hasFilters" class="mt-3 flex justify-end">
                <button @click="clearFilters" class="text-sm text-red-600 hover:bg-red-50 rounded px-3 py-1">Тозалаш</button>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">№</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Мурожаатчи</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Маҳалла</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Категория</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Муҳимлик</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ҳолат</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Юборилди</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">SLA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="(a, i) in appeals.data" :key="a.id" class="hover:bg-gray-50 cursor-pointer"
                        @click="router.get(`/appeals/${a.id}`)">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ (appeals.current_page - 1) * appeals.per_page + i + 1 }}</td>
                        <td class="px-4 py-3 text-sm">
                            <p class="font-medium text-gray-900">{{ a.applicant_name }}</p>
                            <p v-if="a.applicant_phone" class="text-xs text-gray-400">{{ a.applicant_phone }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ a.mahalla?.name_cyr ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ a.category?.name_cyr ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="APPEAL_PRIORITY_COLORS[a.priority]">
                                {{ APPEAL_PRIORITY_LABELS[a.priority] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="APPEAL_STATUS_COLORS[a.status]">
                                {{ APPEAL_STATUS_LABELS[a.status] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ a.submitted_at ? formatDateShort(a.submitted_at) : '—' }}</td>
                        <td class="px-4 py-3 text-xs">
                            <span v-if="isOverdue(a)" class="font-semibold text-red-600">Муддат ўтган</span>
                            <span v-else-if="a.sla_due_at" class="text-gray-500">{{ formatDateShort(a.sla_due_at) }}</span>
                            <span v-else class="text-gray-400">—</span>
                        </td>
                    </tr>
                    <tr v-if="appeals.data.length === 0">
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Мурожаатлар топилмади</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
