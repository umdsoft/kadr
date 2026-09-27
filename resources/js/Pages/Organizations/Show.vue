<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';

const props = defineProps<{
    organization: Record<string, any>;
    canManageUsers: boolean;
}>();

const { success } = useFlash();
const showUserForm = ref(false);

const roleLabels: Record<string, string> = {
    'tashkilot-admin': 'Ташкилот админи',
    'tashkilot-xodimi': 'Ташкилот ходими',
};

const userForm = useForm({
    name: '',
    login: '',
    password: '',
    role: 'tashkilot-admin',
});

function createUser() {
    userForm.post(`/organizations/${props.organization.id}/users`, {
        preserveScroll: true,
        onSuccess: () => {
            userForm.reset();
            showUserForm.value = false;
        },
    });
}

function deleteUser(userId: number) {
    if (confirm('Бу фойдаланувчини ўчиришга ишончингиз комилми?')) {
        router.delete(`/organizations/${props.organization.id}/users/${userId}`, { preserveScroll: true });
    }
}

function deleteOrg() {
    if (confirm(`"${props.organization.name_cyr}" ташкилотини ўчиришга ишончингиз комилми?`)) {
        router.delete(`/organizations/${props.organization.id}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ organization.name_cyr }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ organization.kompleks?.name_cyr ?? 'Комплекс белгиланмаган' }}</p>
            </div>
            <div class="flex gap-2">
                <Link :href="`/organizations/${organization.id}/edit`"
                    class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">Таҳрирлаш</Link>
                <button @click="deleteOrg" class="rounded-md border border-red-200 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">Ўчириш</button>
            </div>
        </div>

        <!-- Ma'lumotlar -->
        <div class="mb-6 grid grid-cols-1 gap-4 rounded-lg bg-white p-6 shadow sm:grid-cols-2">
            <div><p class="text-xs text-gray-400">СТИР</p><p class="text-sm text-gray-800">{{ organization.inn ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-400">Телефон</p><p class="text-sm text-gray-800">{{ organization.phone ?? '—' }}</p></div>
            <div class="sm:col-span-2"><p class="text-xs text-gray-400">Манзил</p><p class="text-sm text-gray-800">{{ organization.address ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-400">Яратган</p><p class="text-sm text-gray-800">{{ organization.creator?.name ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-400">Ҳолат</p>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="organization.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                    {{ organization.is_active ? 'Фаол' : 'Нофаол' }}
                </span>
            </div>
        </div>

        <!-- Foydalanuvchilar -->
        <div class="rounded-lg bg-white shadow">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-gray-900">Фойдаланувчилар (логинлар)</h2>
                <button v-if="canManageUsers" @click="showUserForm = !showUserForm"
                    class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ showUserForm ? 'Ёпиш' : '+ Логин очиш' }}
                </button>
            </div>

            <!-- Login ochish formasi -->
            <form v-if="showUserForm && canManageUsers" @submit.prevent="createUser" class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Исм</label>
                        <input v-model="userForm.name" type="text" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        <p v-if="userForm.errors.name" class="mt-1 text-xs text-red-600">{{ userForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Логин</label>
                        <input v-model="userForm.login" type="text" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        <p v-if="userForm.errors.login" class="mt-1 text-xs text-red-600">{{ userForm.errors.login }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Парол</label>
                        <input v-model="userForm.password" type="text" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        <p v-if="userForm.errors.password" class="mt-1 text-xs text-red-600">{{ userForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Рол</label>
                        <select v-model="userForm.role" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            <option value="tashkilot-admin">Ташкилот админи</option>
                            <option value="tashkilot-xodimi">Ташкилот ходими</option>
                        </select>
                    </div>
                </div>
                <button type="submit" :disabled="userForm.processing"
                    class="mt-4 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                    {{ userForm.processing ? 'Яратилмоқда...' : 'Логин яратиш' }}
                </button>
            </form>

            <table class="min-w-full divide-y divide-gray-200">
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="u in (organization.users ?? [])" :key="u.id" class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ u.name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ u.login }}</td>
                        <td class="px-6 py-3 text-sm">
                            <span v-for="r in (u.roles ?? [])" :key="r.id" class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                {{ roleLabels[r.name] ?? r.name }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <button v-if="canManageUsers" @click="deleteUser(u.id)" class="text-sm text-red-600 hover:text-red-800">Ўчириш</button>
                        </td>
                    </tr>
                    <tr v-if="(organization.users ?? []).length === 0">
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Ҳали логин очилмаган</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
