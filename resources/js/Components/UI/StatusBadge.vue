<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    status: string;
    labels: Record<string, string>;
    colors: Record<string, string>;
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), { size: 'sm' });

const sizeClass = computed(() => props.size === 'md'
    ? 'px-3 py-1 text-sm'
    : 'px-2.5 py-0.5 text-xs');
</script>

<template>
    <span class="inline-block rounded-full font-medium" :class="[colors[status] ?? 'bg-gray-100 text-gray-600', sizeClass]">
        {{ labels[status] ?? status }}
    </span>
</template>
