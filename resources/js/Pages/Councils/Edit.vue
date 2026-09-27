<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import CouncilForm from '@/Components/Councils/CouncilForm.vue';
import type { MahallaCouncil } from '@/types';

const props = defineProps<{
    council: MahallaCouncil;
    mahallas: { id: number; name_cyr: string }[];
    users: { id: number; name: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ council.mahalla?.name_cyr }} — еттиликни таҳрирлаш</h1>
        </div>
        <CouncilForm :council="council" :mahallas="mahallas" :users="users"
            :submit-url="`/councils/${council.id}`" method="put" submit-label="Сақлаш" />
    </AppLayout>
</template>
