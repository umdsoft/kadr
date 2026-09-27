<script setup lang="ts">
defineProps<{
    label: string;
    error?: string;
    required?: boolean;
    hint?: string;
}>();
</script>

<template>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>
        <slot :hasError="!!error" />
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-xs text-gray-400">{{ hint }}</p>
    </div>
</template>
