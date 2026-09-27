<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import HyForm from '@/Components/HokimYordamchilari/HyForm.vue';
import type { HokimYordamchisi } from '@/types';

defineProps<{
    item: HokimYordamchisi;
    users: { id: number; name: string }[];
    mahallas: { id: number; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Таҳрирлаш: {{ item.full_name_cyr }}</h1>
        </div>
        <HyForm :item="item" :users="users" :mahallas="mahallas"
            :submit-url="`/hokim-yordamchilari/${item.id}`" method="put" submit-label="Сақлаш" />
    </AppLayout>
</template>
