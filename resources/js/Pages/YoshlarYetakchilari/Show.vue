<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useFlash } from '@/Composables/useFlash';
import { useFormatters } from '@/Composables/useFormatters';

import { YY_EVENT_TYPE_LABELS as eventLabels } from '@/Constants/labels';
import type { YoshlarYetakchisi } from '@/types';

const props = defineProps<{ item: YoshlarYetakchisi }>();
const { success } = useFlash();
const { formatDateShort } = useFormatters();

function deleteItem() {
    if (confirm('Ёшлар етакчисини ўчиришга ишончингиз комилми?')) {
        router.delete(`/yoshlar-yetakchilari/${props.item.id}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ item.full_name_cyr }}</h1>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                        {{ item.is_active ? 'Фаол' : 'Архив' }}
                    </span>
                </div>
                <p v-if="item.mahalla" class="text-sm text-gray-500">{{ item.mahalla.name_cyr }}</p>
            </div>
            <div class="flex gap-2">
                <Link :href="`/yoshlar-yetakchilari/${item.id}/edit`"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Таҳрирлаш</Link>
                <button @click="deleteItem"
                    class="rounded-md border border-red-300 bg-white px-4 py-2 text-sm text-red-600 hover:bg-red-50">Ўчириш</button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h2 class="font-semibold text-gray-900">Маълумотлар</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div class="grid grid-cols-2 px-4 py-2.5" v-for="field in [
                        { label: 'Ф.И.Ш.', value: item.full_name_cyr },
                        { label: 'Телефон', value: item.phone ?? '—' },
                        { label: 'Туғилган санаси', value: item.birth_date ? formatDateShort(item.birth_date) : '—' },
                        { label: 'Маҳалла', value: item.mahalla?.name_cyr ?? '—' },
                        { label: 'Бошланиш санаси', value: formatDateShort(item.start_date) },
                        { label: 'Тугаш санаси', value: item.end_date ? formatDateShort(item.end_date) : '—' },
                        { label: 'Тизим фойдаланувчиси', value: item.user?.name ?? '—' },
                    ]" :key="field.label">
                        <span class="text-sm font-medium text-gray-600">{{ field.label }}</span>
                        <span class="text-sm text-gray-900">{{ field.value }}</span>
                    </div>
                </div>
                <div v-if="item.notes" class="border-t border-gray-100 px-4 py-3">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700">Изоҳлар</h3>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ item.notes }}</p>
                </div>
            </div>

            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h2 class="font-semibold text-gray-900">Тадбирлар ({{ item.events?.length ?? 0 }})</h2>
                </div>
                <div v-if="item.events && item.events.length > 0" class="divide-y divide-gray-100">
                    <div v-for="ev in item.events" :key="ev.id" class="px-4 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium text-gray-900">{{ ev.title }}</p>
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-700">
                                {{ eventLabels[ev.event_type] }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ formatDateShort(ev.event_date) }} · {{ ev.participants_count }} қатнашчи
                        </p>
                    </div>
                </div>
                <p v-else class="p-4 text-sm text-gray-400">Тадбирлар йўқ</p>
            </div>
        </div>
    </AppLayout>
</template>
