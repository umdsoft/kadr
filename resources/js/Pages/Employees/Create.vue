<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Step1Header from '@/Components/EmployeeWizard/Step1Header.vue';
import Step2Personal from '@/Components/EmployeeWizard/Step2Personal.vue';
import type { WorkHistory, Relative } from '@/types';

const currentStep = ref(1);
const totalSteps = 4;

const form = useForm({
    // 1-блок
    last_name_cyr: '',
    first_name_cyr: '',
    middle_name_cyr: '',
    last_name_lat: '',
    first_name_lat: '',
    middle_name_lat: '',
    current_position: '',
    position_start_date: '',
    photo_path: null as string | null,
    // 2-блок
    birth_date: '',
    birth_place: '',
    birth_region_id: null as number | null,
    birth_district_id: null as number | null,
    nationality: '',
    party_affiliation: 'йўқ',
    education_level: '',
    education_completion: '',
    specialty_by_education: '',
    academic_degree: 'йўқ',
    academic_title: 'йўқ',
    foreign_languages: 'йўқ',
    state_awards: 'тақдирланмаган',
    elected_body_member: 'йўқ',
    jshshir: '',
    passport_series: '',
    passport_number: '',
    department_id: null as number | null,
    position_id: null as number | null,
});

// 3-блок ва 4-блок — alohida saqlanadi (Phase 4 route lar orqali)
// Hozircha wizard faqat 1-2 blokni yaratadi

const stepLabels = ['Сарлавҳа', 'Шахсий маълумотлар', 'Меҳнат фаолияти', 'Қариндошлар'];

function updateField(field: string, value: unknown) {
    (form as Record<string, unknown>)[field] = value;
}

function nextStep() {
    if (currentStep.value < totalSteps) {
        currentStep.value++;
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
}

function submit() {
    form.post('/employees', {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги ходим қўшиш</h1>
        </div>

        <!-- Қадамлар индикатори -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <template v-for="(label, index) in stepLabels" :key="index">
                    <div class="flex items-center">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold"
                            :class="currentStep > index + 1
                                ? 'bg-green-600 text-white'
                                : currentStep === index + 1
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-600'"
                        >
                            {{ index + 1 }}
                        </div>
                        <span class="ml-2 text-sm" :class="currentStep === index + 1 ? 'font-semibold text-gray-900' : 'text-gray-500'">
                            {{ label }}
                        </span>
                    </div>
                    <div v-if="index < stepLabels.length - 1" class="mx-4 h-px flex-1 bg-gray-300" />
                </template>
            </div>
        </div>

        <!-- Форма -->
        <div class="rounded-lg bg-white p-6 shadow">
            <form @submit.prevent="submit">
                <!-- 1-қадам -->
                <div v-show="currentStep === 1">
                    <Step1Header :form="form" :errors="form.errors" @update="updateField" />
                </div>

                <!-- 2-қадам -->
                <div v-show="currentStep === 2">
                    <Step2Personal :form="form" :errors="form.errors" @update="updateField" />
                </div>

                <!-- 3-қадам (Phase 7 да тўлдирилади — hozir xabar) -->
                <div v-show="currentStep === 3">
                    <div class="py-12 text-center text-gray-500">
                        <p class="text-lg">Меҳнат фаолияти</p>
                        <p class="mt-2 text-sm">Ходим яратилгандан кейин, профиль саҳифасидан қўшиш мумкин.</p>
                    </div>
                </div>

                <!-- 4-қадам -->
                <div v-show="currentStep === 4">
                    <div class="py-12 text-center text-gray-500">
                        <p class="text-lg">Яқин қариндошлар</p>
                        <p class="mt-2 text-sm">Ходим яратилгандан кейин, профиль саҳифасидан қўшиш мумкин.</p>
                    </div>
                </div>

                <!-- Навигация тугмалари -->
                <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-4">
                    <button
                        v-if="currentStep > 1"
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        @click="prevStep"
                    >
                        Орқага
                    </button>
                    <div v-else />

                    <div class="flex gap-3">
                        <button
                            v-if="currentStep < totalSteps"
                            type="button"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                            @click="nextStep"
                        >
                            Кейинги қадам
                        </button>
                        <button
                            v-else
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-green-600 px-6 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Сақланмоқда...</span>
                            <span v-else>Сақлаш</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
