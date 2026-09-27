<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import MeetingForm from '@/Components/Appeals/MeetingForm.vue';
import type { YouthMeeting } from '@/types';

const props = defineProps<{
    meeting: YouthMeeting;
    users: { id: number; name: string }[];
    mahallas: { id: number; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Учрашувни таҳрирлаш</h1>
        </div>
        <MeetingForm :meeting="meeting" :users="users" :mahallas="mahallas"
            :submit-url="`/meetings/${meeting.id}`" method="put" submit-label="Сақлаш" />
    </AppLayout>
</template>
