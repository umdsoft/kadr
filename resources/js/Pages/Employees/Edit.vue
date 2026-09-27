<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Step1Header from '@/Components/EmployeeWizard/Step1Header.vue';
import Step2Personal from '@/Components/EmployeeWizard/Step2Personal.vue';
import Step3WorkHistory from '@/Components/EmployeeWizard/Step3WorkHistory.vue';
import Step4Relatives from '@/Components/EmployeeWizard/Step4Relatives.vue';
import type { Employee, WorkHistory, Relative } from '@/types';

const props = defineProps<{
    employee: Employee;
}>();

const photoFile = ref<File | null>(null);

// 3-блок: Меҳнат фаолияти — мавжуд ёзувлар
const workHistory = ref<WorkHistory[]>([...(props.employee.work_history ?? [])]);
const whForm = useForm({ work_history: [] as WorkHistory[] });
function saveWorkHistory() {
    whForm.transform(() => ({ work_history: workHistory.value })).put(`/employees/${props.employee.id}/work-history`, {
        preserveScroll: true,
    });
}

// 4-блок: Яқин қариндошлар — мавжуд ёзувлар
const relatives = ref<Relative[]>([...(props.employee.relatives ?? [])]);
const relForm = useForm({ relatives: [] as Relative[] });
function saveRelatives() {
    relForm.transform(() => ({ relatives: relatives.value })).put(`/employees/${props.employee.id}/relatives`, {
        preserveScroll: true,
    });
}

const form = useForm({
    _method: 'PUT',
    last_name_cyr: props.employee.last_name_cyr,
    first_name_cyr: props.employee.first_name_cyr,
    middle_name_cyr: props.employee.middle_name_cyr,
    last_name_lat: props.employee.last_name_lat ?? '',
    first_name_lat: props.employee.first_name_lat ?? '',
    middle_name_lat: props.employee.middle_name_lat ?? '',
    current_position: props.employee.current_position,
    position_start_date: props.employee.position_start_date,
    photo_path: props.employee.photo_path,
    birth_date: props.employee.birth_date,
    birth_place: props.employee.birth_place,
    birth_region_id: props.employee.birth_region_id,
    birth_district_id: props.employee.birth_district_id,
    nationality: props.employee.nationality,
    party_affiliation: props.employee.party_affiliation,
    education_level: props.employee.education_level,
    education_completion: props.employee.education_completion,
    specialty_by_education: props.employee.specialty_by_education,
    academic_degree: props.employee.academic_degree,
    academic_title: props.employee.academic_title,
    foreign_languages: props.employee.foreign_languages,
    state_awards: props.employee.state_awards,
    elected_body_member: props.employee.elected_body_member,
    jshshir: props.employee.jshshir,
    passport_series: props.employee.passport_series,
    passport_number: props.employee.passport_number,
    department_id: props.employee.department_id,
    position_id: props.employee.position_id,
});

function updateField(field: string, value: unknown) {
    (form as unknown as Record<string, unknown>)[field] = value;
}

function handleFile(field: string, file: File | null) {
    if (field === 'photo') {
        photoFile.value = file;
    }
}

function submit() {
    form.transform((data) => ({
        ...data,
        photo: photoFile.value,
    })).post(`/employees/${props.employee.id}`, {
        forceFormData: true,
    });
}
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Таҳрирлаш: {{ employee.last_name_cyr }} {{ employee.first_name_cyr }}</h1>
            <Link :href="`/employees/${employee.id}`"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Бекор қилиш
            </Link>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <form @submit.prevent="submit">
                <div class="space-y-8">
                    <Step1Header :form="(form as unknown as Record<string, unknown>)" :errors="form.errors" :existing-photo-url="employee.photo_url" @update="updateField" @file="handleFile" />
                    <hr class="border-gray-200" />
                    <Step2Personal :form="(form as unknown as Record<string, unknown>)" :errors="form.errors" @update="updateField" />
                </div>

                <div class="mt-8 flex items-center justify-end border-t border-gray-200 pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Сақланмоқда...</span>
                        <span v-else>Асосий маълумотларни сақлаш</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 3-блок: Меҳнат фаолияти (алоҳида сақланади) -->
        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <Step3WorkHistory :items="workHistory" :errors="whForm.errors" @update="workHistory = $event" />
            <div class="mt-6 flex items-center justify-end border-t border-gray-200 pt-4">
                <button type="button" :disabled="whForm.processing" @click="saveWorkHistory"
                    class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                    <span v-if="whForm.processing">Сақланмоқда...</span>
                    <span v-else>Меҳнат фаолиятини сақлаш</span>
                </button>
            </div>
        </div>

        <!-- 4-блок: Яқин қариндошлар (алоҳида сақланади) -->
        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <Step4Relatives :items="relatives" :errors="relForm.errors" @update="relatives = $event" />
            <div class="mt-6 flex items-center justify-end border-t border-gray-200 pt-4">
                <button type="button" :disabled="relForm.processing" @click="saveRelatives"
                    class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                    <span v-if="relForm.processing">Сақланмоқда...</span>
                    <span v-else>Қариндошларни сақлаш</span>
                </button>
            </div>
        </div>
    </AppLayout>
</template>
