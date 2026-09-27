<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { useFormatters } from '@/Composables/useFormatters';
import { MEETING_STATUS_LABELS, MEETING_STATUS_COLORS, DEBOUNCE_DELAY } from '@/Constants/labels';
import type { PaginatedResponse, YouthMeeting } from '@/types';

const props = defineProps<{
    meetings: PaginatedResponse<YouthMeeting>;
    filters: { search?: string; status?: string };
}>();

const { success } = useFlash();
const { formatDateShort } = useFormatters();
const search = ref(props.filters.search ?? '');

let timer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/meetings', { search: search.value || undefined }, { preserveState: true, preserveScroll: true });
    }, DEBOUNCE_DELAY);
});
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Ёшлар учрашувлари</h1>
            <Link href="/meetings/create" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Янги учрашув
            </Link>
        </div>

        <input v-model="search" type="text" placeholder="Жой бўйича қидириш..."
            class="mb-4 w-full max-w-md rounded-md border border-gray-300 px-3 py-2 text-sm" />

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Сана</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Жой</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Маҳалла</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Раис</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Қатнашчилар</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Мурожаатлар</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ҳолат</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="m in meetings.data" :key="m.id" class="hover:bg-gray-50 cursor-pointer"
                        @click="router.get(`/meetings/${m.id}`)">
                        <td class="px-4 py-3 text-sm">{{ formatDateShort(m.meeting_date) }}<span v-if="m.meeting_time" class="text-gray-400"> {{ m.meeting_time }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ m.location ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ m.mahalla?.name_cyr ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ m.chairman?.name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ m.participants_count }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ m.appeals_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="MEETING_STATUS_COLORS[m.status]">
                                {{ MEETING_STATUS_LABELS[m.status] }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="meetings.data.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Учрашувлар топилмади</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
