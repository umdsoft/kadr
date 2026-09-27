<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';

const props = defineProps<{
    organizations: {
        data: Array<Record<string, any>>;
        current_page: number;
        per_page: number;
        last_page: number;
        total: number;
    };
    filters: { search?: string };
}>();

const { success } = useFlash();
const search = ref(props.filters.search ?? '');

let timer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/organizations', { search: search.value || undefined }, { preserveState: true, preserveScroll: true });
    }, 350);
});
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Ташкилотлар</h1>
            <Link href="/organizations/create" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги ташкилот
            </Link>
        </div>

        <input v-model="search" type="text" placeholder="Ташкилот номи бўйича қидириш..."
            class="mb-4 w-full max-w-md rounded-md border border-gray-300 px-3 py-2 text-sm" />

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Номи</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Комплекс</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">СТИР</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Фойдаланувчилар</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ҳолат</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="o in organizations.data" :key="o.id" class="cursor-pointer hover:bg-gray-50"
                        @click="router.get(`/organizations/${o.id}`)">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ o.name_cyr }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ o.kompleks?.name_cyr ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ o.inn ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ o.users_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="o.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ o.is_active ? 'Фаол' : 'Нофаол' }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="organizations.data.length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Ташкилотлар топилмади</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
