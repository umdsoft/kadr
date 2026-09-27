<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import ControlPlanForm from '@/Components/ControlPlans/ControlPlanForm.vue';

defineProps<{
    plan: any;
    users: { id: string; name: string }[];
    organizations: { id: string; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Назорат режани таҳрирлаш</h1>
        </div>
        <ControlPlanForm
            :users="users"
            :organizations="organizations"
            :plan="plan"
            :submit-url="`/control-plans/${plan.id}`"
            method="put"
            submit-label="Сақлаш"
            show-status
        />
    </AppLayout>
</template>
