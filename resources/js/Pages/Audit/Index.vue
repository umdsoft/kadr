<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { PaginatedResponse } from '@/types';

interface ActivityItem {
    id: number;
    log_name: string;
    description: string;
    subject_type: string;
    subject_id: number | null;
    causer: string;
    properties: {
        old?: Record<string, unknown>;
        attributes?: Record<string, unknown>;
    };
    created_at: string;
}

const props = defineProps<{
    activities: PaginatedResponse<ActivityItem>;
    filters: { search?: string; subject_type?: string };
}>();

const search = ref(props.filters.search ?? '');

let debounceTimer: ReturnType<typeof setTimeout>;

watch(search, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/audit', { search: val || undefined }, { preserveState: true });
    }, 300);
});

function formatProperties(item: ActivityItem): string {
    if (!item.properties?.attributes) return '';
    const attrs = item.properties.attributes;
    return Object.entries(attrs)
        .map(([k, v]) => `${k}: ${v}`)
        .join(', ');
}
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Аудит журнали</h1>
        </div>

        <div class="mb-4">
            <input
                v-model="search"
                type="text"
                placeholder="Қидириш..."
                class="w-full max-w-md rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            />
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Сана</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Фойдаланувчи</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Амал</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Объект</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ўзгаришлар</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="item in activities.data" :key="item.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-500">{{ item.created_at }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-900">{{ item.causer }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-700">{{ item.description }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-500">
                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">{{ item.subject_type }}</span>
                            <span v-if="item.subject_id" class="ml-1 text-xs text-gray-400">#{{ item.subject_id }}</span>
                        </td>
                        <td class="max-w-xs truncate px-4 py-2.5 text-xs text-gray-500">
                            {{ formatProperties(item) || '—' }}
                        </td>
                    </tr>
                    <tr v-if="activities.data.length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                            Ёзувлар топилмади
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Саҳифалаш -->
            <div v-if="activities.last_page > 1" class="border-t border-gray-200 px-4 py-3">
                <p class="text-sm text-gray-500">
                    Жами {{ activities.total }} та ёзувдан {{ activities.from }}—{{ activities.to }} кўрсатилмоқда
                </p>
            </div>
        </div>
    </AppLayout>
</template>
