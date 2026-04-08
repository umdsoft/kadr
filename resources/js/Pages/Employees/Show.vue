<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import type { Employee } from '@/types';

const props = defineProps<{
    employee: Employee;
}>();

const { success } = useFlash();
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
            {{ success }}
        </div>

        <!-- Сарлавҳа -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ employee.last_name_cyr }} {{ employee.first_name_cyr }} {{ employee.middle_name_cyr }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ employee.current_position }}</p>
            </div>
            <div class="flex gap-2">
                <a :href="`/employees/${employee.id}/edit`"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Таҳрирлаш
                </a>
                <a :href="`/employees/${employee.id}/export/malumotnoma`"
                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                    DOCX юклаш
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Чап панел: Шахсий маълумотлар -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 2-блок: Шахсий маълумотлар жадвали -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h2 class="font-semibold text-gray-900">Шахсий маълумотлар</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="grid grid-cols-2 px-4 py-2.5" v-for="field in [
                            { label: 'Туғилган санаси', value: employee.birth_date },
                            { label: 'Туғилган жойи', value: employee.birth_place },
                            { label: 'Миллати', value: employee.nationality },
                            { label: 'Партиявийлиги', value: employee.party_affiliation },
                            { label: 'Маълумоти', value: employee.education_level },
                            { label: 'Қаерни тамомлаган', value: employee.education_completion },
                            { label: 'Мутахассислиги', value: employee.specialty_by_education },
                            { label: 'Илмий даражаси', value: employee.academic_degree },
                            { label: 'Илмий унвони', value: employee.academic_title },
                            { label: 'Чет тиллари', value: employee.foreign_languages },
                            { label: 'Давлат мукофотлари', value: employee.state_awards },
                            { label: 'Сайланадиган органлар', value: employee.elected_body_member },
                        ]" :key="field.label">
                            <span class="text-sm font-medium text-gray-600">{{ field.label }}</span>
                            <span class="text-sm text-gray-900">{{ field.value }}</span>
                        </div>
                    </div>
                </div>

                <!-- 3-блок: Меҳнат фаолияти -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h2 class="font-semibold text-gray-900">Меҳнат фаолияти</h2>
                    </div>
                    <table v-if="employee.work_history && employee.work_history.length > 0" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Йиллар</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Ташкилот ва лавозим</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="wh in employee.work_history" :key="wh.id">
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900">
                                    {{ wh.start_year }}–{{ wh.end_year ?? 'ҳ.в.' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    {{ wh.organization_full }}, {{ wh.position_full }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="p-4 text-sm text-gray-500">Маълумот мавжуд эмас</p>
                </div>

                <!-- 4-блок: Яқин қариндошлар -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h2 class="font-semibold text-gray-900">Яқин қариндошлар</h2>
                    </div>
                    <div v-if="employee.relatives && employee.relatives.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Қариндошлиги</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Ф.И.Ш.</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Туғилган</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Иш жойи</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Турар жойи</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="rel in employee.relatives" :key="rel.id">
                                    <td class="whitespace-nowrap px-3 py-2 text-sm font-medium text-gray-900">{{ rel.relationship_type }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ rel.full_name_cyr }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ rel.birth_year }} й., {{ rel.birth_place }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ rel.workplace_and_position }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ rel.residence_full }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="p-4 text-sm text-gray-500">Маълумот мавжуд эмас</p>
                </div>
            </div>

            <!-- Ўнг панел: Хизмат маълумотлари -->
            <div class="space-y-4">
                <div class="rounded-lg bg-white p-4 shadow">
                    <h3 class="mb-3 font-semibold text-gray-900">Хизмат маълумотлари</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Бўлими:</span>
                            <span class="ml-1 font-medium">{{ employee.department?.name_cyr ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Лавозими:</span>
                            <span class="ml-1 font-medium">{{ employee.position?.name_cyr ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Тайинланган:</span>
                            <span class="ml-1 font-medium">{{ employee.position_start_date }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
