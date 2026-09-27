<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Step1Header from '@/Components/EmployeeWizard/Step1Header.vue';
import Step2Personal from '@/Components/EmployeeWizard/Step2Personal.vue';
import Step3WorkHistory from '@/Components/EmployeeWizard/Step3WorkHistory.vue';
import Step4Relatives from '@/Components/EmployeeWizard/Step4Relatives.vue';
import type { WorkHistory, Relative } from '@/types';

const currentStep = ref(1);
const totalSteps = 4;

const photoFile = ref<File | null>(null);

function handleFile(field: string, file: File | null) {
    if (field === 'photo') {
        photoFile.value = file;
    }
}

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
    photo: null as File | null,
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

// 3-блок: Меҳнат фаолияти
const workHistory = ref<WorkHistory[]>([]);

function updateWorkHistory(items: WorkHistory[]) {
    workHistory.value = items;
}

// 4-блок: Яқин қариндошлар
const relatives = ref<Relative[]>([]);

function updateRelatives(items: Relative[]) {
    relatives.value = items;
}

const stepLabels = ['Сарлавҳа', 'Шахсий маълумотлар', 'Меҳнат фаолияти', 'Қариндошлар'];

function updateField(field: string, value: unknown) {
    (form as unknown as Record<string, unknown>)[field] = value;
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
    form.transform((data) => ({
        ...data,
        photo: photoFile.value,
        work_history: workHistory.value,
        relatives: relatives.value,
    })).post('/employees', {
        onSuccess: () => {
            form.reset();
            workHistory.value = [];
            relatives.value = [];
        },
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
                <Step1Header v-if="currentStep === 1" :form="(form as unknown as Record<string, unknown>)" :errors="form.errors" @update="updateField" @file="handleFile" />

                <!-- 2-қадам -->
                <Step2Personal v-if="currentStep === 2" :form="(form as unknown as Record<string, unknown>)" :errors="form.errors" @update="updateField" />

                <!-- 3-қадам -->
                <Step3WorkHistory v-if="currentStep === 3" :items="workHistory" :errors="form.errors" @update="updateWorkHistory" />

                <!-- 4-қадам -->
                <Step4Relatives v-if="currentStep === 4" :items="relatives" :errors="form.errors" @update="updateRelatives" />

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
