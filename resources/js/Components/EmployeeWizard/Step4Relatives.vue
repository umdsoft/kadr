<script setup lang="ts">
import { RELATIONSHIP_TYPES } from '@/types';
import type { Relative } from '@/types';

const props = defineProps<{
    items: Relative[];
    errors: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'update', items: Relative[]): void;
}>();

function addRow() {
    const items = [...props.items, {
        relationship_type: '',
        full_name_cyr: '',
        birth_year: 1990,
        birth_place: '',
        is_deceased: false,
        deceased_year: null,
        workplace_and_position: '',
        former_position: '',
        residence_full: '',
    }];
    emit('update', items);
}

function removeRow(index: number) {
    emit('update', props.items.filter((_, i) => i !== index));
}

function updateField(index: number, field: string, value: unknown) {
    const items = [...props.items];
    (items[index] as unknown as Record<string, unknown>)[field] = value;
    // Вафот этган checkbox ўзгарса — майдонларни тозалаш
    if (field === 'is_deceased' && !value) {
        (items[index] as unknown as Record<string, unknown>).deceased_year = null;
        (items[index] as unknown as Record<string, unknown>).former_position = '';
    }
    emit('update', items);
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">4-қадам: Яқин қариндошлар</h2>
            <button type="button" @click="addRow"
                class="rounded-md bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">
                + Қариндош қўшиш
            </button>
        </div>

        <div v-for="(item, index) in items" :key="index"
            class="rounded-md border border-gray-200 bg-gray-50 p-4">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-600">{{ index + 1 }}-қариндош</span>
                <button type="button" @click="removeRow(index)"
                    class="text-sm text-red-600 hover:text-red-800">Ўчириш</button>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs text-gray-600">Қариндошлиги *</label>
                    <select :value="item.relationship_type"
                        @change="updateField(index, 'relationship_type', ($event.target as HTMLSelectElement).value)"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                        :class="{ 'border-red-500': errors[`relatives.${index}.relationship_type`] }">
                        <option value="">Танланг</option>
                        <option v-for="rt in RELATIONSHIP_TYPES" :key="rt" :value="rt">{{ rt }}</option>
                    </select>
                    <p v-if="errors[`relatives.${index}.relationship_type`]" class="mt-1 text-xs text-red-600">
                        {{ errors[`relatives.${index}.relationship_type`] }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs text-gray-600">Тўлиқ Ф.И.Ш. *</label>
                    <input type="text" :value="item.full_name_cyr"
                        @input="updateField(index, 'full_name_cyr', ($event.target as HTMLInputElement).value)"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                        :class="{ 'border-red-500': errors[`relatives.${index}.full_name_cyr`] }"
                        placeholder="Фамилияси Исми Отасининг исми" />
                    <p v-if="errors[`relatives.${index}.full_name_cyr`]" class="mt-1 text-xs text-red-600">
                        {{ errors[`relatives.${index}.full_name_cyr`] }}</p>
                </div>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs text-gray-600">Туғилган йили *</label>
                    <input type="number" :value="item.birth_year" min="1920" :max="new Date().getFullYear()"
                        @input="updateField(index, 'birth_year', Number(($event.target as HTMLInputElement).value))"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs text-gray-600">Туғилган жойи *</label>
                    <input type="text" :value="item.birth_place"
                        @input="updateField(index, 'birth_place', ($event.target as HTMLInputElement).value)"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                        :class="{ 'border-red-500': errors[`relatives.${index}.birth_place`] }"
                        placeholder="Хоразм вилояти, Урганч шаҳри" />
                    <p v-if="errors[`relatives.${index}.birth_place`]" class="mt-1 text-xs text-red-600">
                        {{ errors[`relatives.${index}.birth_place`] }}</p>
                </div>
            </div>

            <!-- Вафот этган -->
            <div class="mt-3 flex items-center gap-2">
                <input type="checkbox" :checked="item.is_deceased"
                    @change="updateField(index, 'is_deceased', ($event.target as HTMLInputElement).checked)"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600" />
                <label class="text-sm text-gray-700">Вафот этган</label>
            </div>

            <div v-if="item.is_deceased" class="mt-3 grid grid-cols-1 gap-3 rounded border border-yellow-200 bg-yellow-50 p-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs text-gray-600">Вафот этган йили *</label>
                    <input type="number" :value="item.deceased_year"
                        @input="updateField(index, 'deceased_year', Number(($event.target as HTMLInputElement).value))"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                    <p v-if="errors[`relatives.${index}.deceased_year`]" class="mt-1 text-xs text-red-600">
                        {{ errors[`relatives.${index}.deceased_year`] }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-gray-600">Аввалги лавозими *</label>
                    <input type="text" :value="item.former_position"
                        @input="updateField(index, 'former_position', ($event.target as HTMLInputElement).value)"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                        placeholder="1-марказий поликлиника шифокори" />
                </div>
            </div>

            <div v-if="!item.is_deceased" class="mt-3">
                <label class="mb-1 block text-xs text-gray-600">Иш жойи ва лавозими *</label>
                <input type="text" :value="item.workplace_and_position"
                    @input="updateField(index, 'workplace_and_position', ($event.target as HTMLInputElement).value)"
                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                    :class="{ 'border-red-500': errors[`relatives.${index}.workplace_and_position`] }" />
                <p v-if="errors[`relatives.${index}.workplace_and_position`]" class="mt-1 text-xs text-red-600">
                    {{ errors[`relatives.${index}.workplace_and_position`] }}</p>
            </div>

            <div class="mt-3">
                <label class="mb-1 block text-xs text-gray-600">Яшаш манзили *</label>
                <input type="text" :value="item.residence_full"
                    @input="updateField(index, 'residence_full', ($event.target as HTMLInputElement).value)"
                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                    :class="{ 'border-red-500': errors[`relatives.${index}.residence_full`] }"
                    placeholder="Хоразм вилояти, Урганч шаҳри, Навоий кўчаси 10-уй" />
                <p v-if="errors[`relatives.${index}.residence_full`]" class="mt-1 text-xs text-red-600">
                    {{ errors[`relatives.${index}.residence_full`] }}</p>
            </div>
        </div>

        <p v-if="items.length === 0" class="rounded-md border border-dashed border-gray-300 py-8 text-center text-sm text-gray-500">
            Қариндошлар ҳали қўшилмаган. «Қариндош қўшиш» тугмасини босинг.
        </p>
    </div>
</template>
