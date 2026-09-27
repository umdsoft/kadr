<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import YyForm from '@/Components/YoshlarYetakchilari/YyForm.vue';

defineProps<{
    users: { id: number; name: string }[];
    mahallas: { id: number; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги ёшлар етакчиси</h1>
        </div>
        <YyForm :users="users" :mahallas="mahallas" submit-url="/yoshlar-yetakchilari" method="post" submit-label="Қўшиш" />
    </AppLayout>
</template>
