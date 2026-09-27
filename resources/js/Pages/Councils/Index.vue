<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { DEBOUNCE_DELAY } from '@/Constants/labels';
import type { PaginatedResponse, MahallaCouncil } from '@/types';

const props = defineProps<{
    councils: PaginatedResponse<MahallaCouncil>;
    filters: { search?: string };
}>();

const { success } = useFlash();
const search = ref(props.filters.search ?? '');

let timer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/councils', { search: search.value || undefined }, { preserveState: true, preserveScroll: true });
    }, DEBOUNCE_DELAY);
});
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Маҳалла еттиликлари</h1>
            <Link href="/councils/create" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги еттилик
            </Link>
        </div>

        <input v-model="search" type="text" placeholder="Маҳалла бўйича қидириш..."
            class="mb-4 w-full max-w-md rounded-md border border-gray-300 px-3 py-2 text-sm" />

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Маҳалла</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Еттилик номи</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Аъзолар</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Телефон</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ҳолат</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="c in councils.data" :key="c.id" class="hover:bg-gray-50 cursor-pointer"
                        @click="router.get(`/councils/${c.id}`)">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ c.mahalla?.name_cyr ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ c.name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ c.members_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ c.phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="c.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ c.is_active ? 'Фаол' : 'Архив' }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="councils.data.length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Маҳалла еттиликлари топилмади</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
