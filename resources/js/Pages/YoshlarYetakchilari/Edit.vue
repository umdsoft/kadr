<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import YyForm from '@/Components/YoshlarYetakchilari/YyForm.vue';
import type { YoshlarYetakchisi } from '@/types';

defineProps<{
    item: YoshlarYetakchisi;
    users: { id: number; name: string }[];
    mahallas: { id: number; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Таҳрирлаш: {{ item.full_name_cyr }}</h1>
        </div>
        <YyForm :item="item" :users="users" :mahallas="mahallas"
            :submit-url="`/yoshlar-yetakchilari/${item.id}`" method="put" submit-label="Сақлаш" />
    </AppLayout>
</template>
