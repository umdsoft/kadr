<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import type { Employee, PaginatedResponse, SearchFilters } from '@/types';

const props = defineProps<{
    employees: PaginatedResponse<Employee>;
    filters: SearchFilters;
}>();

const { success } = useFlash();

const search = ref(props.filters.search ?? '');
const departmentId = ref(props.filters.department_id ?? null);

let debounceTimer: ReturnType<typeof setTimeout>;

function applyFilters() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/employees', {
            search: search.value || undefined,
            department_id: departmentId.value || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
}

watch(search, applyFilters);
watch(departmentId, applyFilters);

function goToPage(url: string | null) {
    if (url) router.get(url, {}, { preserveState: true });
}
</script>

<template>
    <AppLayout>
        <!-- Flash xabar -->
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
            {{ success }}
        </div>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Ходимлар рўйхати</h1>
            <a
                href="/employees/create"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                + Янги ходим
            </a>
        </div>

        <!-- Қидирув -->
        <div class="mb-4 flex gap-4">
            <div class="flex-1">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Ф.И.Ш. бўйича қидириш..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
            </div>
        </div>

        <!-- Жадвал -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">№</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ф.И.Ш.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Лавозими</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Бўлими</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Маълумоти</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Амаллар</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr
                        v-for="(employee, index) in employees.data"
                        :key="employee.id"
                        class="hover:bg-gray-50"
                    >
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                            {{ (employees.current_page - 1) * employees.per_page + index + 1 }}
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <a :href="`/employees/${employee.id}`" class="text-blue-600 hover:text-blue-800">
                                {{ employee.last_name_cyr }} {{ employee.first_name_cyr }} {{ employee.middle_name_cyr }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ employee.current_position.length > 50
                                ? employee.current_position.substring(0, 50) + '...'
                                : employee.current_position }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ employee.department?.name_cyr ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ employee.education_level }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <a :href="`/employees/${employee.id}`" class="text-blue-600 hover:text-blue-800">Кўриш</a>
                            <span class="mx-1 text-gray-300">|</span>
                            <a :href="`/employees/${employee.id}/edit`" class="text-yellow-600 hover:text-yellow-800">Таҳрирлаш</a>
                            <span class="mx-1 text-gray-300">|</span>
                            <a
                                :href="`/employees/${employee.id}/export/malumotnoma`"
                                class="text-green-600 hover:text-green-800"
                            >DOCX</a>
                        </td>
                    </tr>
                    <tr v-if="employees.data.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                            Ходимлар топилмади
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Саҳифалаш -->
            <div v-if="employees.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Жами <span class="font-medium">{{ employees.total }}</span> тадан
                        <span class="font-medium">{{ employees.from }}</span>—<span class="font-medium">{{ employees.to }}</span> кўрсатилмоқда
                    </p>
                    <div class="flex gap-1">
                        <template v-for="page in employees.last_page" :key="page">
                            <button
                                class="rounded px-3 py-1 text-sm"
                                :class="page === employees.current_page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'"
                                @click="goToPage(`/employees?page=${page}&search=${search}`)"
                            >
                                {{ page }}
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
