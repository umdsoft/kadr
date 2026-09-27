<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { useGeoCatalog } from '@/Composables/useGeoCatalog';
import { EDUCATION_LEVELS } from '@/types';

const props = defineProps<{
    form: Record<string, unknown>;
    errors: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'update', field: string, value: unknown): void;
}>();

const { regions, districts, nationalities, fetchAll, fetchDistricts } = useGeoCatalog();

onMounted(() => fetchAll());

// immediate: true — Edit rejimida oldindan tanlangan region uchun tumanlar darhol yuklanadi
// (aks holda tuman dropdown bo'sh ko'rinardi).
watch(() => props.form.birth_region_id, (val) => {
    if (val) fetchDistricts(val as string);
}, { immediate: true });

function update(field: string, event: Event) {
    const target = event.target as HTMLInputElement | HTMLSelectElement;
    emit('update', field, target.value);
}

function updateSelect(field: string, event: Event) {
    const target = event.target as HTMLSelectElement;
    // ID'lar UUID (satr) — Number() ГА АЙЛАНТИРМАЙМИЗ (Number(UUID)=NaN бўларди).
    emit('update', field, target.value);
}
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">2-қадам: Шахсий маълумотлар</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Туғилган санаси *</label>
                <input type="date" :value="form.birth_date as string" @input="update('birth_date', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.birth_date }" />
                <p v-if="errors.birth_date" class="mt-1 text-xs text-red-600">{{ errors.birth_date }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Миллати *</label>
                <select @change="update('nationality', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">Танланг</option>
                    <option v-for="n in nationalities" :key="n.id" :value="n.name_cyr"
                        :selected="form.nationality === n.name_cyr">{{ n.name_cyr }}</option>
                </select>
                <p v-if="errors.nationality" class="mt-1 text-xs text-red-600">{{ errors.nationality }}</p>
            </div>
        </div>

        <!-- Туғилган жойи — dropdown лар -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Туғилган вилояти *</label>
                <select @change="updateSelect('birth_region_id', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">Танланг</option>
                    <option v-for="r in regions" :key="r.id" :value="r.id"
                        :selected="form.birth_region_id == r.id">{{ r.name_cyr }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Туғилган тумани *</label>
                <select @change="updateSelect('birth_district_id', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">Танланг</option>
                    <option v-for="d in districts" :key="d.id" :value="d.id"
                        :selected="form.birth_district_id == d.id">{{ d.name_cyr }}</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Маълумоти *</label>
                <select @change="update('education_level', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">Танланг</option>
                    <option v-for="el in EDUCATION_LEVELS" :key="el.value" :value="el.value"
                        :selected="form.education_level === el.value">{{ el.label }}</option>
                </select>
                <p v-if="errors.education_level" class="mt-1 text-xs text-red-600">{{ errors.education_level }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Мутахассислиги *</label>
                <input type="text" :value="form.specialty_by_education as string" @input="update('specialty_by_education', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                <p v-if="errors.specialty_by_education" class="mt-1 text-xs text-red-600">{{ errors.specialty_by_education }}</p>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Қаерни тамомлаган *</label>
            <input type="text" :value="form.education_completion as string" @input="update('education_completion', $event)"
                placeholder="2012 йил, Урганч давлат университети (кундузги)"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.education_completion }" />
            <p v-if="errors.education_completion" class="mt-1 text-xs text-red-600">{{ errors.education_completion }}</p>
        </div>

        <!-- Қолган майдонлар -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Партиявийлиги</label>
                <input type="text" :value="form.party_affiliation as string" @input="update('party_affiliation', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Илмий даражаси</label>
                <input type="text" :value="form.academic_degree as string" @input="update('academic_degree', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Илмий унвони</label>
                <input type="text" :value="form.academic_title as string" @input="update('academic_title', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Чет тиллари</label>
                <input type="text" :value="form.foreign_languages as string" @input="update('foreign_languages', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Давлат мукофотлари</label>
                <input type="text" :value="form.state_awards as string" @input="update('state_awards', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.state_awards }" />
                <p v-if="errors.state_awards" class="mt-1 text-xs text-red-600">{{ errors.state_awards }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Сайланадиган орган аъзолиги</label>
                <input type="text" :value="form.elected_body_member as string" @input="update('elected_body_member', $event)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
            </div>
        </div>

    </div>
</template>
