<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import ControlPlanForm from '@/Components/ControlPlans/ControlPlanForm.vue';

defineProps<{
    users: { id: string; name: string }[];
    organizations: { id: string; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги назорат режа</h1>
        </div>
        <ControlPlanForm :users="users" :organizations="organizations" submit-url="/control-plans" method="post" submit-label="Яратиш" />
    </AppLayout>
</template>
