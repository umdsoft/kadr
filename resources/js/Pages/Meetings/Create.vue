<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import MeetingForm from '@/Components/Appeals/MeetingForm.vue';

defineProps<{
    users: { id: number; name: string }[];
    mahallas: { id: number; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги учрашув</h1>
        </div>
        <MeetingForm :users="users" :mahallas="mahallas" submit-url="/meetings" method="post" submit-label="Яратиш" />
    </AppLayout>
</template>
