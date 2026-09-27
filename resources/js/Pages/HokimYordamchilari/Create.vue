<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import HyForm from '@/Components/HokimYordamchilari/HyForm.vue';

defineProps<{
    users: { id: number; name: string }[];
    mahallas: { id: number; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги ҳоким ёрдамчиси</h1>
        </div>
        <HyForm :users="users" :mahallas="mahallas" submit-url="/hokim-yordamchilari" method="post" submit-label="Қўшиш" />
    </AppLayout>
</template>
