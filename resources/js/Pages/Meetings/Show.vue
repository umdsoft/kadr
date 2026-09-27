<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useFlash } from '@/Composables/useFlash';
import { useFormatters } from '@/Composables/useFormatters';
import {
    MEETING_STATUS_LABELS, MEETING_STATUS_COLORS,
    APPEAL_STATUS_LABELS, APPEAL_STATUS_COLORS,
} from '@/Constants/labels';
import type { YouthMeeting } from '@/types';

const props = defineProps<{ meeting: YouthMeeting }>();
const { success } = useFlash();
const { formatDateShort } = useFormatters();

function deleteMeeting() {
    if (confirm('Учрашувни ўчиришга ишончингиз комилми?')) {
        router.delete(`/meetings/${props.meeting.id}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ formatDateShort(meeting.meeting_date) }}</h1>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="MEETING_STATUS_COLORS[meeting.status]">
                        {{ MEETING_STATUS_LABELS[meeting.status] }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">{{ meeting.location ?? 'Жой кўрсатилмаган' }}</p>
            </div>
            <div class="flex gap-2">
                <Link :href="`/appeals/create?meeting_id=${meeting.id}`"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Мурожаат</Link>
                <Link :href="`/meetings/${meeting.id}/edit`"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Таҳрирлаш</Link>
                <button @click="deleteMeeting"
                    class="rounded-md border border-red-300 bg-white px-4 py-2 text-sm text-red-600 hover:bg-red-50">Ўчириш</button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-5">
                <div class="rounded-lg bg-white p-5 shadow space-y-2 text-sm">
                    <h3 class="font-semibold text-gray-900 mb-2">Маълумотлар</h3>
                    <div><span class="text-gray-500">Маҳалла:</span> {{ meeting.mahalla?.name_cyr ?? '—' }}</div>
                    <div><span class="text-gray-500">Раис:</span> {{ meeting.chairman?.name ?? '—' }}</div>
                    <div><span class="text-gray-500">Қатнашчилар:</span> {{ meeting.participants_count }}</div>
                </div>

                <div v-if="meeting.agenda" class="rounded-lg bg-white p-5 shadow">
                    <h3 class="font-semibold text-gray-900 mb-2">Кун тартиби</h3>
                    <p class="whitespace-pre-wrap text-sm text-gray-700">{{ meeting.agenda }}</p>
                </div>

                <div v-if="meeting.notes" class="rounded-lg bg-white p-5 shadow">
                    <h3 class="font-semibold text-gray-900 mb-2">Изоҳлар</h3>
                    <p class="whitespace-pre-wrap text-sm text-gray-700">{{ meeting.notes }}</p>
                </div>

                <div v-if="meeting.ai_summary" class="rounded-lg bg-purple-50 border border-purple-200 p-5">
                    <h3 class="font-semibold text-purple-900 mb-2">🤖 АИ хулоса</h3>
                    <p class="whitespace-pre-wrap text-sm text-purple-800">{{ meeting.ai_summary }}</p>
                </div>
            </div>

            <div>
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h3 class="font-semibold text-gray-900">Мурожаатлар ({{ meeting.appeals?.length ?? 0 }})</h3>
                    </div>
                    <div v-if="meeting.appeals?.length" class="divide-y divide-gray-100">
                        <Link v-for="a in meeting.appeals" :key="a.id" :href="`/appeals/${a.id}`"
                            class="block px-4 py-3 hover:bg-gray-50">
                            <p class="text-sm font-medium text-gray-900">{{ a.applicant_name }}</p>
                            <p class="text-xs text-gray-500">{{ a.category?.name_cyr ?? '—' }}</p>
                            <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-medium" :class="APPEAL_STATUS_COLORS[a.status]">
                                {{ APPEAL_STATUS_LABELS[a.status] }}
                            </span>
                        </Link>
                    </div>
                    <p v-else class="p-4 text-sm text-gray-400">Мурожаатлар йўқ</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
