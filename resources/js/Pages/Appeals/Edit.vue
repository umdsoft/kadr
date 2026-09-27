<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import AppealForm from '@/Components/Appeals/AppealForm.vue';
import type { CitizenAppeal, AppealCategory } from '@/types';

const props = defineProps<{
    appeal: CitizenAppeal;
    categories: AppealCategory[];
    mahallas: { id: number; name_cyr: string }[];
    meetings: { id: number; meeting_date: string; location: string | null }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Таҳрирлаш: {{ appeal.applicant_name }}</h1>
        </div>
        <AppealForm :appeal="appeal" :categories="categories" :mahallas="mahallas" :meetings="meetings"
            :submit-url="`/appeals/${appeal.id}`" method="put" submit-label="Сақлаш" />
    </AppLayout>
</template>
