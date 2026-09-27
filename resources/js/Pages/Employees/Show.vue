<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import type { Employee, PageProps } from '@/types';

const props = defineProps<{
    employee: Employee;
}>();

const { success } = useFlash();
const page = usePage<PageProps>();

const canDelete = computed(() => {
    const perms = page.props.auth?.permissions ?? [];
    const roles = page.props.auth?.roles ?? [];
    return perms.includes('kadrlar.delete') || roles.includes('super-admin');
});

function destroyEmployee() {
    const name = `${props.employee.last_name_cyr} ${props.employee.first_name_cyr}`;
    if (confirm(`${name} ходимни архивга ўтказишни тасдиқлайсизми?`)) {
        router.delete(`/employees/${props.employee.id}`);
    }
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    return `${dd}.${mm}.${d.getFullYear()}`;
}

const birthPlace = computed(() => {
    const parts: string[] = [];
    if (props.employee.birth_region) parts.push(props.employee.birth_region.name_cyr);
    if (props.employee.birth_district) parts.push(props.employee.birth_district.name_cyr);
    if (parts.length > 0) return parts.join(', ');
    return props.employee.birth_place || '—';
});
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
            {{ success }}
        </div>

        <!-- Сарлавҳа -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div v-if="employee.photo_url" class="h-20 w-16 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200">
                    <img :src="employee.photo_url" :alt="employee.full_name" class="h-full w-full object-cover" />
                </div>
                <div v-else class="flex h-20 w-16 flex-shrink-0 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 text-xs text-gray-400">
                    3×4
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ employee.last_name_cyr }} {{ employee.first_name_cyr }} {{ employee.middle_name_cyr }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ employee.current_position }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <Link :href="`/employees/${employee.id}/edit`"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Таҳрирлаш
                </Link>
                <a :href="`/employees/${employee.id}/export/malumotnoma`"
                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                    DOCX юклаш
                </a>
                <button v-if="canDelete" type="button" @click="destroyEmployee"
                    class="rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                    Архивга
                </button>
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
                            { label: 'Туғилган санаси', value: formatDate(employee.birth_date) },
                            { label: 'Туғилган жойи', value: birthPlace },
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
