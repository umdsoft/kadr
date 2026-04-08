<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Stats {
    total_employees: number;
    active_employees: number;
    archived_employees: number;
    departments_count: number;
}

interface DeptStat {
    department: string;
    count: number;
}

interface EduStat {
    level: string;
    count: number;
}

interface ActivityItem {
    id: number;
    description: string;
    subject_type: string;
    causer: string;
    created_at: string;
    properties: Record<string, unknown>;
}

const props = defineProps<{
    stats: Stats;
    by_department: DeptStat[];
    by_education: EduStat[];
    recent_activity: ActivityItem[];
}>();

// Бўлимлар бўйича энг кўп ходимни ҳисоблаш (progress bar учун)
const maxDeptCount = computed(() =>
    Math.max(...props.by_department.map(d => d.count), 1),
);
const maxEduCount = computed(() =>
    Math.max(...props.by_education.map(e => e.count), 1),
);

// Маълумот даражаси рангларини аниқлаш
function eduColor(level: string): string {
    const colors: Record<string, string> = {
        'олий': 'bg-emerald-500',
        'тугалланмаган олий': 'bg-sky-500',
        'ўрта махсус': 'bg-amber-500',
        'ўрта': 'bg-slate-400',
    };
    return colors[level] ?? 'bg-slate-300';
}

function eduBadge(level: string): string {
    const colors: Record<string, string> = {
        'олий': 'bg-emerald-50 text-emerald-700',
        'тугалланмаган олий': 'bg-sky-50 text-sky-700',
        'ўрта махсус': 'bg-amber-50 text-amber-700',
        'ўрта': 'bg-slate-100 text-slate-600',
    };
    return colors[level] ?? 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <AppLayout>
        <!-- Саҳифа сарлавҳаси -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Бош саҳифа</h1>
            <p class="mt-1 text-sm text-slate-500">Хоразм вилояти ҳокимлиги кадрлар бошқаруви</p>
        </div>

        <!-- Статистик карточкалар -->
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Жами ходимлар -->
            <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-blue-50 transition-transform group-hover:scale-110" />
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ stats.total_employees }}</p>
                    <p class="mt-1 text-sm text-slate-500">Жами ходимлар</p>
                </div>
            </div>

            <!-- Фаол -->
            <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-50 transition-transform group-hover:scale-110" />
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ stats.active_employees }}</p>
                    <p class="mt-1 text-sm text-slate-500">Фаол ходимлар</p>
                </div>
            </div>

            <!-- Архивда -->
            <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-amber-50 transition-transform group-hover:scale-110" />
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ stats.archived_employees }}</p>
                    <p class="mt-1 text-sm text-slate-500">Архивдаги</p>
                </div>
            </div>

            <!-- Бўлимлар -->
            <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-violet-50 transition-transform group-hover:scale-110" />
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100">
                            <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ stats.departments_count }}</p>
                    <p class="mt-1 text-sm text-slate-500">Фаол бўлимлар</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <!-- Бўлимлар бўйича тақсимот -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-slate-900">Бўлимлар бўйича</h2>
                        <p class="text-xs text-slate-400">Ходимлар тақсимоти</p>
                    </div>
                    <a href="/employees" class="flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">
                        Барчаси
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
                <div class="p-5">
                    <div v-if="by_department.length > 0" class="space-y-4">
                        <div v-for="item in by_department" :key="item.department">
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-sm text-slate-700">{{ item.department }}</span>
                                <span class="text-sm font-semibold text-slate-900">{{ item.count }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-blue-500 transition-all duration-500"
                                    :style="{ width: `${(item.count / maxDeptCount) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center py-8 text-slate-400">
                        <svg class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        <p class="text-sm">Маълумот мавжуд эмас</p>
                    </div>
                </div>
            </div>

            <!-- Маълумот даражаси бўйича -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-slate-900">Маълумот даражаси</h2>
                        <p class="text-xs text-slate-400">Таълим бўйича тақсимот</p>
                    </div>
                </div>
                <div class="p-5">
                    <div v-if="by_education.length > 0" class="space-y-4">
                        <div v-for="item in by_education" :key="item.level">
                            <div class="mb-1.5 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium" :class="eduBadge(item.level)">
                                        {{ item.level }}
                                    </span>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">{{ item.count }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="eduColor(item.level)"
                                    :style="{ width: `${(item.count / maxEduCount) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center py-8 text-slate-400">
                        <svg class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                        <p class="text-sm">Маълумот мавжуд эмас</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Охирги ўзгаришлар -->
        <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Охирги ўзгаришлар</h2>
                    <p class="text-xs text-slate-400">Тизимдаги сўнгги амаллар</p>
                </div>
                <a href="/audit"
                    class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    Барчасини кўриш
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                <div v-for="item in recent_activity" :key="item.id"
                    class="flex items-center gap-4 px-5 py-3.5 transition-colors hover:bg-slate-50">
                    <!-- Иконка -->
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                        :class="item.description === 'created' ? 'bg-emerald-100' : item.description === 'deleted' ? 'bg-red-100' : 'bg-blue-100'">
                        <svg v-if="item.description === 'created'" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <svg v-else-if="item.description === 'deleted'" class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        <svg v-else class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                    <!-- Контент -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-900">{{ item.description }}</span>
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500">{{ item.subject_type }}</span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-400">{{ item.causer }}</p>
                    </div>
                    <!-- Сана -->
                    <span class="shrink-0 text-xs text-slate-400">{{ item.created_at }}</span>
                </div>
                <div v-if="recent_activity.length === 0" class="flex flex-col items-center py-12 text-slate-400">
                    <svg class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="text-sm">Ҳали ўзгаришлар мавжуд эмас</p>
                    <p class="mt-1 text-xs">Ходим қўшганингизда бу ерда кўринади</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
