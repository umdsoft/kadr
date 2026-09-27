<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { ROLE_LABELS as roleLabels, DEBOUNCE_DELAY } from '@/Constants/labels';
import type { PaginatedResponse, User } from '@/types';

const props = defineProps<{
    users: PaginatedResponse<User>;
    filters: { search?: string };
}>();

const { success } = useFlash();
const search = ref(props.filters.search ?? '');

let timer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/users', { search: search.value || undefined }, { preserveState: true, preserveScroll: true });
    }, DEBOUNCE_DELAY);
});

function deleteUser(user: User) {
    if (confirm(`"${user.name}" фойдаланувчини ўчиришга ишончингиз комилми?`)) {
        router.delete(`/users/${user.id}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Фойдаланувчилар</h1>
            <Link href="/users/create" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги фойдаланувчи
            </Link>
        </div>

        <div class="mb-4">
            <input v-model="search" type="text" placeholder="Исм ёки email бўйича қидириш..."
                class="w-full max-w-md rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">№</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Исми</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Логин</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Роли</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ҳокимлик</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Лавозими</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Амаллар</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="(u, i) in users.data" :key="u.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ (users.current_page - 1) * users.per_page + i + 1 }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ u.name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ u.login }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span v-for="role in u.roles" :key="role.id"
                                class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="role.name === 'super-admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'">
                                {{ roleLabels[role.name] ?? role.name }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ u.department?.name_cyr ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ u.position?.name_cyr ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <Link :href="`/users/${u.id}/edit`" class="text-yellow-600 hover:text-yellow-800">Таҳрирлаш</Link>
                            <span class="mx-1 text-gray-300">|</span>
                            <button @click="deleteUser(u)" class="text-red-600 hover:text-red-800">Ўчириш</button>
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Фойдаланувчилар топилмади</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
