<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useFlash } from '@/Composables/useFlash';
import { COUNCIL_ROLE_LABELS } from '@/Constants/labels';
import type { MahallaCouncil } from '@/types';

const props = defineProps<{ council: MahallaCouncil }>();
const { success } = useFlash();

function deleteCouncil() {
    if (confirm('Маҳалла еттилигини ўчиришга ишончингиз комилми?')) {
        router.delete(`/councils/${props.council.id}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ council.mahalla?.name_cyr }}</h1>
                <p class="text-sm text-gray-500">{{ council.name }}</p>
            </div>
            <div class="flex gap-2">
                <Link :href="`/councils/${council.id}/edit`"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Таҳрирлаш</Link>
                <button @click="deleteCouncil"
                    class="rounded-md border border-red-300 bg-white px-4 py-2 text-sm text-red-600 hover:bg-red-50">Ўчириш</button>
            </div>
        </div>

        <div class="rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="font-semibold text-gray-900">Аъзолар ({{ council.members?.length ?? 0 }})</h2>
            </div>
            <div v-if="council.members?.length" class="divide-y divide-gray-100">
                <div v-for="m in council.members" :key="m.id" class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ m.full_name }}</p>
                        <p class="text-xs text-gray-500">{{ COUNCIL_ROLE_LABELS[m.role] }}<span v-if="m.phone"> · {{ m.phone }}</span></p>
                    </div>
                    <span v-if="m.user" class="text-xs text-blue-600">{{ m.user.name }}</span>
                </div>
            </div>
            <p v-else class="p-4 text-sm text-gray-400">Аъзолар киритилмаган</p>
        </div>
    </AppLayout>
</template>
