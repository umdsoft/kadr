<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface Task { id: number; title: string; date: string; status: string }

const props = defineProps<{ tasks: Task[] }>();

const monthNames = ['Январ', 'Феврал', 'Март', 'Апрел', 'Май', 'Июн', 'Июл', 'Август', 'Сентябр', 'Октябр', 'Ноябр', 'Декабр'];
const weekDays = ['Душ', 'Сеш', 'Чор', 'Пай', 'Жум', 'Шан', 'Якш'];
const statusDot: Record<string, string> = {
    not_started: 'bg-gray-400', in_progress: 'bg-blue-500', completed: 'bg-green-500', overdue: 'bg-red-500',
};

function pad(n: number): string {
    return String(n).padStart(2, '0');
}

const now = new Date();
const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
const cursor = ref(new Date(now.getFullYear(), now.getMonth(), 1));

const tasksByDate = computed(() => {
    const map: Record<string, Task[]> = {};
    for (const t of props.tasks) {
        (map[t.date] ??= []).push(t);
    }
    return map;
});

const label = computed(() => `${monthNames[cursor.value.getMonth()]} ${cursor.value.getFullYear()}`);

const weeks = computed(() => {
    const year = cursor.value.getFullYear();
    const month = cursor.value.getMonth();
    const startOffset = (new Date(year, month, 1).getDay() + 6) % 7; // dushanba = 0
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const cells: { date: string | null; day: number; isToday: boolean; tasks: Task[] }[] = [];
    for (let i = 0; i < startOffset; i++) {
        cells.push({ date: null, day: 0, isToday: false, tasks: [] });
    }
    for (let d = 1; d <= daysInMonth; d++) {
        const ds = `${year}-${pad(month + 1)}-${pad(d)}`;
        cells.push({ date: ds, day: d, isToday: ds === todayStr, tasks: tasksByDate.value[ds] ?? [] });
    }
    while (cells.length % 7 !== 0) {
        cells.push({ date: null, day: 0, isToday: false, tasks: [] });
    }

    const result = [];
    for (let i = 0; i < cells.length; i += 7) {
        result.push(cells.slice(i, i + 7));
    }
    return result;
});

function prev() {
    cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() - 1, 1);
}
function next() {
    cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() + 1, 1);
}
function goToday() {
    cursor.value = new Date(now.getFullYear(), now.getMonth(), 1);
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-semibold text-slate-900">🗓 Топшириқлар тақвими</h2>
                <p class="text-xs text-slate-400">Қайси санада қайси топшириқ бажарилиши керак</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-slate-700">{{ label }}</span>
                <button @click="prev" class="rounded-md border border-slate-200 px-2 py-1 text-slate-500 hover:bg-slate-50">‹</button>
                <button @click="goToday" class="rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-500 hover:bg-slate-50">Бугун</button>
                <button @click="next" class="rounded-md border border-slate-200 px-2 py-1 text-slate-500 hover:bg-slate-50">›</button>
            </div>
        </div>

        <div class="p-3">
            <div class="grid grid-cols-7 border-b border-slate-100 pb-1.5 text-center text-xs font-semibold text-slate-500">
                <div v-for="w in weekDays" :key="w">{{ w }}</div>
            </div>
            <div v-for="(week, wi) in weeks" :key="wi" class="grid grid-cols-7">
                <div v-for="(cell, ci) in week" :key="ci"
                    class="min-h-[78px] border-b border-r border-slate-100 p-1.5 first:border-l"
                    :class="cell.isToday ? 'bg-blue-50/60' : (cell.date ? '' : 'bg-slate-50/40')">
                    <template v-if="cell.date">
                        <div class="mb-1 text-right text-base font-semibold leading-none"
                            :class="cell.isToday ? 'text-blue-600' : 'text-slate-700'">
                            <span v-if="cell.isToday" class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-sm text-white">{{ cell.day }}</span>
                            <span v-else>{{ cell.day }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <button v-for="t in cell.tasks.slice(0, 2)" :key="t.id" @click="router.get(`/topshiriqlar/${t.id}`)"
                                class="flex w-full items-center gap-1 truncate rounded bg-slate-50 px-1 py-0.5 text-left text-[10px] text-slate-700 hover:bg-slate-100">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full" :class="statusDot[t.status] ?? 'bg-gray-400'"></span>
                                <span class="truncate">{{ t.title }}</span>
                            </button>
                            <p v-if="cell.tasks.length > 2" class="px-1 text-[10px] text-slate-400">+{{ cell.tasks.length - 2 }} яна</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
