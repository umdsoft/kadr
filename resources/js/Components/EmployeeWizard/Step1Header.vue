<script setup lang="ts">
/**
 * 1-БЛОК: Сарлавҳа — Ф.И.Ш., лавозим, тайинланган сана, расм.
 */
const props = defineProps<{
    form: Record<string, unknown>;
    errors: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'update', field: string, value: unknown): void;
}>();

function update(field: string, event: Event) {
    emit('update', field, (event.target as HTMLInputElement).value);
}
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">1-қадам: Сарлавҳа маълумотлари</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Фамилияси (Кирилл) *</label>
                <input
                    type="text" :value="form.last_name_cyr as string" @input="update('last_name_cyr', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.last_name_cyr }"
                />
                <p v-if="errors.last_name_cyr" class="mt-1 text-xs text-red-600">{{ errors.last_name_cyr }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Исми (Кирилл) *</label>
                <input
                    type="text" :value="form.first_name_cyr as string" @input="update('first_name_cyr', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.first_name_cyr }"
                />
                <p v-if="errors.first_name_cyr" class="mt-1 text-xs text-red-600">{{ errors.first_name_cyr }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Отасининг исми (Кирилл) *</label>
                <input
                    type="text" :value="form.middle_name_cyr as string" @input="update('middle_name_cyr', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.middle_name_cyr }"
                />
                <p v-if="errors.middle_name_cyr" class="mt-1 text-xs text-red-600">{{ errors.middle_name_cyr }}</p>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Ҳозирги лавозими (тўлиқ) *</label>
            <textarea
                :value="form.current_position as string" @input="update('current_position', $event)"
                rows="2"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.current_position }"
            />
            <p v-if="errors.current_position" class="mt-1 text-xs text-red-600">{{ errors.current_position }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Тайинланган сана *</label>
                <input
                    type="date" :value="form.position_start_date as string" @input="update('position_start_date', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.position_start_date }"
                />
                <p v-if="errors.position_start_date" class="mt-1 text-xs text-red-600">{{ errors.position_start_date }}</p>
            </div>
        </div>
    </div>
</template>
