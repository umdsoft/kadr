<script setup lang="ts">
interface Props {
    open: boolean;
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'primary';
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Тасдиқлаш',
    confirmText: 'Тасдиқлаш',
    cancelText: 'Бекор қилиш',
    variant: 'danger',
});

const emit = defineEmits<{
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="emit('cancel')">
        <div class="mx-4 w-full max-w-md rounded-lg bg-white shadow-xl">
            <div class="p-5">
                <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ message }}</p>
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-3">
                <button @click="emit('cancel')"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    {{ cancelText }}
                </button>
                <button @click="emit('confirm')"
                    class="rounded-md px-4 py-2 text-sm font-semibold text-white"
                    :class="variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'">
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </div>
</template>
