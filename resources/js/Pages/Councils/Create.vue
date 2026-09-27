<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import CouncilForm from '@/Components/Councils/CouncilForm.vue';

defineProps<{
    mahallas: { id: number; name_cyr: string }[];
    users: { id: number; name: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги маҳалла еттилиги</h1>
        </div>
        <CouncilForm :mahallas="mahallas" :users="users" submit-url="/councils" method="post" submit-label="Яратиш" />
    </AppLayout>
</template>
