<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { DEBOUNCE_DELAY, HY_DIRECTION_LABELS as directionLabels } from '@/Constants/labels';
import type { PaginatedResponse, HokimYordamchisi } from '@/types';

const props = defineProps<{
    items: PaginatedResponse<HokimYordamchisi>;
    filters: { search?: string; direction?: string; is_active?: string };
}>();

const { success } = useFlash();
const search = ref(props.filters.search ?? '');
const direction = ref(props.filters.direction ?? null);

let timer: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/hokim-yordamchilari', {
            search: search.value || undefined,
            direction: direction.value || undefined,
        }, { preserveState: true, preserveScroll: true });
    }, DEBOUNCE_DELAY);
}

watch(search, applyFilters);

function deleteItem(id: number) {
    if (confirm('Ҳоким ёрдамчисини ўчиришга ишончингиз комилми?')) {
        router.delete(`/hokim-yordamchilari/${id}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Ҳоким ёрдамчилари</h1>
            <Link href="/hokim-yordamchilari/create"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги ёрдамчи
            </Link>
        </div>

        <div class="mb-4 flex gap-3">
            <input v-model="search" type="text" placeholder="Ф.И.Ш. бўйича қидириш..."
                class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm" />
            <select v-model="direction" @change="applyFilters" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                <option :value="null">Барча йўналишлар</option>
                <option v-for="(label, key) in directionLabels" :key="key" :value="key">{{ label }}</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">№</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ф.И.Ш.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Йўналиш</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Маҳалла</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Топшириқлар</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ҳолати</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Амаллар</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="(item, i) in items.data" :key="item.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ (items.current_page - 1) * items.per_page + i + 1 }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <Link :href="`/hokim-yordamchilari/${item.id}`" class="text-blue-600 hover:text-blue-800">
                                {{ item.full_name_cyr }}
                            </Link>
                            <p v-if="item.phone" class="text-xs text-gray-400">{{ item.phone }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ directionLabels[item.direction] ?? item.direction }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ item.mahalla?.name_cyr ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ item.assignments_count }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ item.is_active ? 'Фаол' : 'Архив' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <Link :href="`/hokim-yordamchilari/${item.id}`" class="text-blue-600 hover:text-blue-800">Кўриш</Link>
                            <span class="mx-1 text-gray-300">|</span>
                            <Link :href="`/hokim-yordamchilari/${item.id}/edit`" class="text-yellow-600 hover:text-yellow-800">Таҳрирлаш</Link>
                            <span class="mx-1 text-gray-300">|</span>
                            <button @click="deleteItem(item.id)" class="text-red-600 hover:text-red-800">Ўчириш</button>
                        </td>
                    </tr>
                    <tr v-if="items.data.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Маълумот мавжуд эмас</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
