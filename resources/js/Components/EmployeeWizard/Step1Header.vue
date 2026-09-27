<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    form: Record<string, unknown>;
    errors: Record<string, string>;
    existingPhotoUrl?: string | null;
}>();

const emit = defineEmits<{
    (e: 'update', field: string, value: unknown): void;
    (e: 'file', field: string, file: File | null): void;
}>();

const photoPreview = ref<string | null>(null);

function update(field: string, event: Event) {
    emit('update', field, (event.target as HTMLInputElement).value);
}

function onPhotoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    emit('file', 'photo', file);

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        photoPreview.value = null;
    }
}
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">1-қадам: Сарлавҳа маълумотлари</h2>

        <div class="flex gap-6">
            <!-- Расм юклаш -->
            <div class="flex-shrink-0">
                <label class="mb-1 block text-sm font-medium text-gray-700">Расм (3×4)</label>
                <div class="relative h-40 w-32 overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50">
                    <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                    <img v-else-if="existingPhotoUrl" :src="existingPhotoUrl" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-center justify-center text-center text-xs text-gray-400 px-2">
                        JPG/PNG<br>max 2 MB
                    </div>
                    <input type="file" accept="image/jpeg,image/png" @change="onPhotoChange"
                        class="absolute inset-0 cursor-pointer opacity-0" />
                </div>
                <p v-if="errors.photo" class="mt-1 text-xs text-red-600">{{ errors.photo }}</p>
            </div>

            <!-- Ф.И.Ш. -->
            <div class="flex-1 space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Фамилияси (Кирилл) *</label>
                        <input type="text" :value="form.last_name_cyr as string" @input="update('last_name_cyr', $event)"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            :class="{ 'border-red-500': errors.last_name_cyr }" />
                        <p v-if="errors.last_name_cyr" class="mt-1 text-xs text-red-600">{{ errors.last_name_cyr }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Исми (Кирилл) *</label>
                        <input type="text" :value="form.first_name_cyr as string" @input="update('first_name_cyr', $event)"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            :class="{ 'border-red-500': errors.first_name_cyr }" />
                        <p v-if="errors.first_name_cyr" class="mt-1 text-xs text-red-600">{{ errors.first_name_cyr }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Отасининг исми (Кирилл) *</label>
                        <input type="text" :value="form.middle_name_cyr as string" @input="update('middle_name_cyr', $event)"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            :class="{ 'border-red-500': errors.middle_name_cyr }" />
                        <p v-if="errors.middle_name_cyr" class="mt-1 text-xs text-red-600">{{ errors.middle_name_cyr }}</p>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Ҳозирги лавозими (тўлиқ) *</label>
                    <textarea :value="form.current_position as string" @input="update('current_position', $event)"
                        rows="2"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.current_position }" />
                    <p v-if="errors.current_position" class="mt-1 text-xs text-red-600">{{ errors.current_position }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
