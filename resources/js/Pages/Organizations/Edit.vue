<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import OrganizationForm from '@/Components/Organizations/OrganizationForm.vue';

const props = defineProps<{
    organization: Record<string, any>;
    komplekslar: { id: number; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Ташкилотни таҳрирлаш</h1>
        </div>
        <OrganizationForm
            :organization="organization"
            :komplekslar="komplekslar"
            :submit-url="`/organizations/${organization.id}`"
            method="put"
            submit-label="Сақлаш"
        />
    </AppLayout>
</template>
