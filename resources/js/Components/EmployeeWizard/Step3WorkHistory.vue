<script setup lang="ts">
import { ref } from 'vue';
import type { WorkHistory } from '@/types';

const props = defineProps<{
    items: WorkHistory[];
    errors: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'update', items: WorkHistory[]): void;
}>();

function addRow() {
    const items = [...props.items, {
        start_year: new Date().getFullYear(),
        end_year: null,
        organization_full: '',
        position_full: '',
        order_number: null,
        order_date: null,
        sort_order: props.items.length,
    }];
    emit('update', items);
}

function removeRow(index: number) {
    const items = props.items.filter((_, i) => i !== index);
    emit('update', items);
}

function updateField(index: number, field: keyof WorkHistory, value: unknown) {
    const items = [...props.items];
    (items[index] as Record<string, unknown>)[field] = value;
    emit('update', items);
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">3-қадам: Меҳнат фаолияти</h2>
            <button type="button" @click="addRow"
                class="rounded-md bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">
                + Қатор қўшиш
            </button>
        </div>

        <div v-for="(item, index) in items" :key="index"
            class="rounded-md border border-gray-200 bg-gray-50 p-4">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-600">{{ index + 1 }}-ёзув</span>
                <button type="button" @click="removeRow(index)"
                    class="text-sm text-red-600 hover:text-red-800">Ўчириш</button>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs text-gray-600">Бошланиш йили *</label>
                    <input type="number" :value="item.start_year" min="1950" :max="new Date().getFullYear()"
                        @input="updateField(index, 'start_year', Number(($event.target as HTMLInputElement).value))"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                    <p v-if="errors[`work_history.${index}.start_year`]" class="mt-1 text-xs text-red-600">
                        {{ errors[`work_history.${index}.start_year`] }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-gray-600">Тугаш йили</label>
                    <input type="number" :value="item.end_year" min="1950" :max="new Date().getFullYear()"
                        @input="updateField(index, 'end_year', ($event.target as HTMLInputElement).value ? Number(($event.target as HTMLInputElement).value) : null)"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                        placeholder="ҳ.в." />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs text-gray-600">Ташкилот (тўлиқ) *</label>
                    <input type="text" :value="item.organization_full"
                        @input="updateField(index, 'organization_full', ($event.target as HTMLInputElement).value)"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                        :class="{ 'border-red-500': errors[`work_history.${index}.organization_full`] }" />
                    <p v-if="errors[`work_history.${index}.organization_full`]" class="mt-1 text-xs text-red-600">
                        {{ errors[`work_history.${index}.organization_full`] }}</p>
                </div>
            </div>
            <div class="mt-3">
                <label class="mb-1 block text-xs text-gray-600">Лавозим (тўлиқ) *</label>
                <input type="text" :value="item.position_full"
                    @input="updateField(index, 'position_full', ($event.target as HTMLInputElement).value)"
                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                    :class="{ 'border-red-500': errors[`work_history.${index}.position_full`] }" />
                <p v-if="errors[`work_history.${index}.position_full`]" class="mt-1 text-xs text-red-600">
                    {{ errors[`work_history.${index}.position_full`] }}</p>
            </div>
        </div>

        <p v-if="items.length === 0" class="rounded-md border border-dashed border-gray-300 py-8 text-center text-sm text-gray-500">
            Меҳнат фаолияти ёзувлари ҳали қўшилмаган. «Қатор қўшиш» тугмасини босинг.
        </p>
    </div>
</template>
