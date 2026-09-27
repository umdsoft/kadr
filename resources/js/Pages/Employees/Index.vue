<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { useGeoCatalog } from '@/Composables/useGeoCatalog';
import { EDUCATION_LEVELS } from '@/types';
import type { Employee, PaginatedResponse, SearchFilters } from '@/types';

const props = defineProps<{
    employees: PaginatedResponse<Employee>;
    filters: SearchFilters;
}>();

const { success } = useFlash();
const { regions, districts, nationalities, departments, fetchAll, fetchDistricts } = useGeoCatalog();

const showFilters = ref(false);
const search = ref(props.filters.search ?? '');
const departmentId = ref(props.filters.department_id ?? null);
const educationLevel = ref(props.filters.education_level ?? null);
const nationality = ref(props.filters.nationality ?? null);
const birthDistrictId = ref(props.filters.birth_district_id ?? null);
const specialty = ref(props.filters.specialty ?? null);
const selectedRegionId = ref<number | null>(null);

onMounted(() => fetchAll());

let debounceTimer: ReturnType<typeof setTimeout>;

// Joriy filtrlar — applyFilters ham, goToPage ham ishlatadi (DRY).
function filterParams() {
    return {
        search: search.value || undefined,
        department_id: departmentId.value || undefined,
        education_level: educationLevel.value || undefined,
        nationality: nationality.value || undefined,
        birth_district_id: birthDistrictId.value || undefined,
        specialty: specialty.value || undefined,
    };
}

function applyFilters() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/employees', filterParams(), {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
}

function clearFilters() {
    search.value = '';
    departmentId.value = null;
    educationLevel.value = null;
    nationality.value = null;
    birthDistrictId.value = null;
    specialty.value = null;
    selectedRegionId.value = null;
    applyFilters();
}

const hasActiveFilters = () => {
    return departmentId.value || educationLevel.value || nationality.value || birthDistrictId.value || specialty.value;
};

watch(search, applyFilters);
watch(selectedRegionId, (val) => {
    if (val) {
        fetchDistricts(val);
        birthDistrictId.value = null;
    }
});

// Sahifaga o'tish — barcha joriy filtrlarni saqlaydi (M4: ilgari faqat search saqlanardi).
function goToPage(page: number) {
    if (page < 1 || page > props.employees.last_page || page === props.employees.current_page) return;
    router.get('/employees', { ...filterParams(), page }, {
        preserveState: true,
        preserveScroll: true,
    });
}

// Kompakt sahifalash oynasi (joriy ±2) — 1000 yozuvda 40 tugma emas.
const pageWindow = computed(() => {
    const total = props.employees.last_page;
    const current = props.employees.current_page;
    const start = Math.max(1, current - 2);
    const end = Math.min(total, current + 2);
    const pages: number[] = [];
    for (let p = start; p <= end; p++) pages.push(p);
    return pages;
});
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
            {{ success }}
        </div>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Ходимлар рўйхати</h1>
            <Link href="/employees/create"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги ходим
            </Link>
        </div>

        <!-- Қидирув + Фильтр тугмаси -->
        <div class="mb-4 flex gap-3">
            <div class="flex-1">
                <input v-model="search" type="text" placeholder="Ф.И.Ш. бўйича қидириш..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </div>
            <button type="button" @click="showFilters = !showFilters"
                class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-medium"
                :class="hasActiveFilters()
                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                    : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Фильтр
                <span v-if="hasActiveFilters()" class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs text-white">!</span>
            </button>
        </div>

        <!-- Фильтр панели -->
        <div v-if="showFilters" class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Бўлим -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Бўлими</label>
                    <select v-model="departmentId" @change="applyFilters"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">Барчаси</option>
                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name_cyr }}</option>
                    </select>
                </div>

                <!-- Маълумоти -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Маълумоти</label>
                    <select v-model="educationLevel" @change="applyFilters"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">Барчаси</option>
                        <option v-for="el in EDUCATION_LEVELS" :key="el.value" :value="el.value">{{ el.label }}</option>
                    </select>
                </div>

                <!-- Миллати -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Миллати</label>
                    <select v-model="nationality" @change="applyFilters"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">Барчаси</option>
                        <option v-for="n in nationalities" :key="n.id" :value="n.name_cyr">{{ n.name_cyr }}</option>
                    </select>
                </div>

                <!-- Мутахассислиги -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Мутахассислиги</label>
                    <input v-model="specialty" @input="applyFilters" type="text" placeholder="Қидириш..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </div>

                <!-- Вилоят -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Туғилган вилояти</label>
                    <select v-model="selectedRegionId"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">Барчаси</option>
                        <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.name_cyr }}</option>
                    </select>
                </div>

                <!-- Туман -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Туғилган тумани</label>
                    <select v-model="birthDistrictId" @change="applyFilters"
                        :disabled="!selectedRegionId"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100">
                        <option :value="null">Барчаси</option>
                        <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name_cyr }}</option>
                    </select>
                </div>
            </div>

            <!-- Тозалаш -->
            <div v-if="hasActiveFilters()" class="mt-3 flex justify-end">
                <button type="button" @click="clearFilters"
                    class="rounded-md px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">
                    Фильтрларни тозалаш
                </button>
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
                    <tr v-for="(employee, index) in employees.data" :key="employee.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                            {{ (employees.current_page - 1) * employees.per_page + index + 1 }}
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <Link :href="`/employees/${employee.id}`" class="text-blue-600 hover:text-blue-800">
                                {{ employee.last_name_cyr }} {{ employee.first_name_cyr }} {{ employee.middle_name_cyr }}
                            </Link>
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
                            <Link :href="`/employees/${employee.id}`" class="text-blue-600 hover:text-blue-800">Кўриш</Link>
                            <span class="mx-1 text-gray-300">|</span>
                            <Link :href="`/employees/${employee.id}/edit`" class="text-yellow-600 hover:text-yellow-800">Таҳрирлаш</Link>
                            <span class="mx-1 text-gray-300">|</span>
                            <a :href="`/employees/${employee.id}/export/malumotnoma`"
                                class="text-green-600 hover:text-green-800">DOCX</a>
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
                    <div class="flex items-center gap-1">
                        <button type="button" :disabled="employees.current_page <= 1"
                            class="rounded border border-gray-300 bg-white px-2.5 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:opacity-40"
                            @click="goToPage(employees.current_page - 1)">‹</button>

                        <button v-if="pageWindow[0] > 1" type="button"
                            class="rounded border border-gray-300 bg-white px-3 py-1 text-sm text-gray-700 hover:bg-gray-100"
                            @click="goToPage(1)">1</button>
                        <span v-if="pageWindow[0] > 2" class="px-1 text-gray-400">…</span>

                        <button v-for="page in pageWindow" :key="page" type="button"
                            class="rounded px-3 py-1 text-sm"
                            :class="page === employees.current_page
                                ? 'bg-blue-600 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'"
                            @click="goToPage(page)">{{ page }}</button>

                        <span v-if="pageWindow[pageWindow.length - 1] < employees.last_page - 1" class="px-1 text-gray-400">…</span>
                        <button v-if="pageWindow[pageWindow.length - 1] < employees.last_page" type="button"
                            class="rounded border border-gray-300 bg-white px-3 py-1 text-sm text-gray-700 hover:bg-gray-100"
                            @click="goToPage(employees.last_page)">{{ employees.last_page }}</button>

                        <button type="button" :disabled="employees.current_page >= employees.last_page"
                            class="rounded border border-gray-300 bg-white px-2.5 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:opacity-40"
                            @click="goToPage(employees.current_page + 1)">›</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
