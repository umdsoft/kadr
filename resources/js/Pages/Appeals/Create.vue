<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import AppealForm from '@/Components/Appeals/AppealForm.vue';
import type { AppealCategory } from '@/types';

defineProps<{
    categories: AppealCategory[];
    mahallas: { id: number; name_cyr: string }[];
    meetings: { id: number; meeting_date: string; location: string | null }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги мурожаат</h1>
            <p class="text-sm text-gray-500">Мурожаатни қабул қилиб, тегишли йўналишга юбориш</p>
        </div>
        <AppealForm :categories="categories" :mahallas="mahallas" :meetings="meetings"
            submit-url="/appeals" method="post" submit-label="Қабул қилиш" />
    </AppLayout>
</template>
