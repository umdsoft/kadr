<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{ status: number }>();

const titles: Record<number, string> = {
    403: 'Кириш тақиқланган',
    404: 'Саҳифа топилмади',
    419: 'Сессия муддати тугади',
    500: 'Сервер хатоси',
    503: 'Тизим хизмат кўрсатмоқда',
};

const descriptions: Record<number, string> = {
    403: 'Ушбу саҳифага кириш ҳуқуқингиз йўқ.',
    404: 'Сиз қидираётган саҳифа мавжуд эмас ёки кўчирилган.',
    419: 'Хавфсизлик сессияси тугади. Илтимос, қайта киринг.',
    500: 'Кутилмаган хатолик юз берди. Илтимос, кейинроқ уриниб кўринг.',
    503: 'Тизим вақтинча хизмат кўрсатиш режимида. Тез орада қайтамиз.',
};

const title = computed(() => titles[props.status] ?? 'Хатолик');
const description = computed(() => descriptions[props.status] ?? 'Кутилмаган хатолик юз берди.');
</script>

<template>
    <Head :title="`${status} — ${title}`" />
    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-6">
        <div class="w-full max-w-md text-center">
            <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600">
                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>

            <p class="text-6xl font-extrabold tracking-tight text-blue-600">{{ status }}</p>
            <h1 class="mt-3 text-2xl font-bold text-slate-900">{{ title }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ description }}</p>

            <Link
                href="/"
                class="mt-8 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.5a.75.75 0 0 0 .75.75H9.75v-6a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v6h4.5a.75.75 0 0 0 .75-.75V9.75" />
                </svg>
                Бош саҳифага қайтиш
            </Link>

            <p class="mt-10 text-xs text-slate-400">Хоразм вилояти ҳокимлиги бошқарув тизими</p>
        </div>
    </div>
</template>
