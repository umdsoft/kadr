<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import OrganizationForm from '@/Components/Organizations/OrganizationForm.vue';

defineProps<{
    komplekslar: { id: number; name_cyr: string }[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги ташкилот</h1>
            <p class="mt-1 text-sm text-gray-500">Ташкилот яратиб, кейин унга админ логин очишингиз мумкин.</p>
        </div>
        <OrganizationForm :komplekslar="komplekslar" submit-url="/organizations" method="post" submit-label="Яратиш" />
    </AppLayout>
</template>
