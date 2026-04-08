<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Step1Header from '@/Components/EmployeeWizard/Step1Header.vue';
import Step2Personal from '@/Components/EmployeeWizard/Step2Personal.vue';
import type { Employee } from '@/types';

const props = defineProps<{
    employee: Employee;
}>();

const form = useForm({
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
    (form as Record<string, unknown>)[field] = value;
}

function submit() {
    form.put(`/employees/${props.employee.id}`);
}
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Таҳрирлаш: {{ employee.last_name_cyr }} {{ employee.first_name_cyr }}</h1>
            <a :href="`/employees/${employee.id}`"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Бекор қилиш
            </a>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <form @submit.prevent="submit">
                <div class="space-y-8">
                    <Step1Header :form="form" :errors="form.errors" @update="updateField" />
                    <hr class="border-gray-200" />
                    <Step2Personal :form="form" :errors="form.errors" @update="updateField" />
                </div>

                <div class="mt-8 flex items-center justify-end border-t border-gray-200 pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Сақланмоқда...</span>
                        <span v-else>Сақлаш</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
