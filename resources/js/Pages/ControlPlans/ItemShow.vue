<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { useFormatters } from '@/Composables/useFormatters';
import {
    EXECUTION_STATUS_LABELS as execLabels,
    EXECUTION_STATUS_BADGE_COLORS as execColors,
    EXECUTION_STATUS_BUTTON_COLORS as execButtonColors,
} from '@/Constants/labels';
import type { ControlPlanItem, ActivityLog, ExecutionStatus } from '@/types';

const props = defineProps<{
    item: ControlPlanItem;
    activities: ActivityLog[];
    canEdit: boolean;
}>();

const { success } = useFlash();
const { formatSize, formatDateTime, formatDateUz: formatDate } = useFormatters();

const selectedStatus = ref<ExecutionStatus>(props.item.execution_status);
const report = ref(props.item.execution_report ?? '');
const uploadFile = ref<File | null>(null);
const uploadDesc = ref('');
const showUpload = ref(false);

function saveStatus() {
    router.put(`/control-plans/items/${props.item.id}/status`, {
        execution_status: selectedStatus.value,
        execution_report: report.value,
    }, { preserveScroll: true });
}

function onFileSelect(e: Event) {
    uploadFile.value = (e.target as HTMLInputElement).files?.[0] ?? null;
}

function submitUpload() {
    if (!uploadFile.value) return;
    const formData = new FormData();
    formData.append('file', uploadFile.value);
    if (uploadDesc.value) formData.append('description', uploadDesc.value);
    router.post(`/control-plans/items/${props.item.id}/documents`, formData, {
        forceFormData: true,
        onSuccess: () => { showUpload.value = false; uploadFile.value = null; uploadDesc.value = ''; },
    });
}

function deleteDoc(docId: number) {
    if (confirm('Ҳужжатни ўчиришга ишончингиз комилми?')) {
        router.delete(`/documents/${docId}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <!-- Орқага -->
        <div class="mb-4">
            <Link :href="`/control-plans/${item.plan?.id}`" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                {{ item.plan?.document_number ?? 'Назорат режа' }}га қайтиш
            </Link>
        </div>

        <!-- Банд сарлавҳаси -->
        <div class="mb-6 rounded-lg bg-white p-5 shadow">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="text-lg font-bold text-blue-700">{{ item.item_number }}-банд</span>
                    <span class="ml-2 rounded-full px-2.5 py-0.5 text-xs font-medium" :class="execColors[item.execution_status]">
                        {{ execLabels[item.execution_status] }}
                    </span>
                </div>
                <span v-if="item.deadline" class="text-sm text-gray-500">Муддат: {{ formatDate(item.deadline) }}</span>
            </div>
            <p class="text-sm text-gray-900 leading-relaxed">{{ item.task_description }}</p>

            <!-- Қўшимча маълумотлар -->
            <div v-if="item.implementation || item.funding_source" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div v-if="item.implementation">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Амалга оширилиш механизми</p>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ item.implementation }}</p>
                </div>
                <div v-if="item.funding_source">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Молиялаштириш манбаси</p>
                    <p class="text-sm text-gray-700">{{ item.funding_source }}</p>
                </div>
            </div>

            <!-- Масъуллар -->
            <div v-if="item.responsibles?.length" class="mt-4">
                <p class="text-xs font-semibold text-gray-500 mb-1">Масъул шахслар</p>
                <div class="flex flex-wrap gap-2">
                    <div v-for="r in item.responsibles" :key="r.id"
                        class="rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm">
                        <span class="font-medium">{{ r.responsible_name }}</span>
                        <span v-if="r.responsible_position" class="text-xs text-gray-500 ml-1">· {{ r.responsible_position }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Чап: Status + Hisobot + Fayllar -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Ҳолат ва ҳисобот -->
                <div v-if="canEdit" class="rounded-lg bg-white p-5 shadow">
                    <h3 class="font-semibold text-gray-900 mb-4">Бажарилиш ҳолати</h3>

                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <button v-for="(label, key) in execLabels" :key="key" type="button"
                                @click="selectedStatus = key as ExecutionStatus"
                                class="rounded-full px-4 py-1.5 text-sm font-medium border-2 transition"
                                :class="selectedStatus === key
                                    ? execButtonColors[key]
                                    : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'">
                                {{ label }}
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Бажарилиши ҳақида маълумот</label>
                        <textarea v-model="report" rows="4"
                            placeholder="Бажарилиш жараёни ҳақида маълумот ёзинг..."
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </div>

                    <button @click="saveStatus"
                        class="rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Сақлаш
                    </button>
                </div>

                <!-- Ҳужжатлар -->
                <div class="rounded-lg bg-white p-5 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Илова ҳужжатлар ({{ item.documents?.length ?? 0 }})</h3>
                        <button v-if="canEdit" @click="showUpload = !showUpload"
                            class="inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Ҳужжат юклаш
                        </button>
                    </div>

                    <!-- Юклаш формаси -->
                    <div v-if="showUpload" class="mb-4 rounded-lg border-2 border-dashed border-blue-300 bg-blue-50/50 p-6">
                        <div class="text-center">
                            <svg class="mx-auto h-10 w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <p class="mt-2 text-sm font-medium text-gray-700">Ҳужжатни танланг</p>
                            <p class="text-xs text-gray-400">Барча форматлар · макс. 20 МБ</p>
                        </div>
                        <div class="mt-4 space-y-3">
                            <input type="file" @change="onFileSelect"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-sm file:text-white file:cursor-pointer hover:file:bg-blue-700" />
                            <input v-model="uploadDesc" type="text" placeholder="Изоҳ (ихтиёрий)"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                            <div class="flex justify-end gap-2">
                                <button @click="showUpload = false"
                                    class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Бекор қилиш</button>
                                <button @click="submitUpload" :disabled="!uploadFile"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                    </svg>
                                    Юклаш
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="(item.documents?.length ?? 0) > 0" class="space-y-2">
                        <div v-for="doc in item.documents" :key="doc.id"
                            class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="h-6 w-6 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-900 text-sm">{{ doc.original_name }}</p>
                                    <p class="text-xs text-gray-400">{{ formatSize(doc.file_size) }} · {{ doc.uploader?.name ?? '' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 ml-3">
                                <a :href="`/documents/${doc.id}/download`"
                                    class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs text-blue-700 hover:bg-blue-100">
                                    Юклаб олиш
                                </a>
                                <button v-if="canEdit" @click="deleteDoc(doc.id)"
                                    class="text-xs text-red-500 hover:text-red-700">Ўчириш</button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 italic py-4 text-center">Ҳужжатлар ҳали юкланмаган</p>
                </div>
            </div>

            <!-- Ўнг: Ўзгаришлар тарихи -->
            <div>
                <div class="rounded-lg bg-white p-5 shadow">
                    <h3 class="font-semibold text-gray-900 mb-4">Ўзгаришлар тарихи</h3>

                    <div v-if="activities.length > 0" class="space-y-3">
                        <div v-for="act in activities" :key="act.id" class="border-l-2 border-blue-200 pl-3 py-1">
                            <p class="text-sm font-medium text-gray-900">{{ act.description }}</p>
                            <p class="text-xs text-gray-500">
                                {{ act.causer?.name ?? 'Тизим' }} · {{ formatDateTime(act.created_at) }}
                            </p>
                            <div v-if="act.properties?.old" class="mt-1 text-xs text-gray-400">
                                <span v-if="act.properties.old.execution_report !== act.properties.attributes?.execution_report">
                                    Ҳисобот янгиланди
                                </span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 italic">Ўзгаришлар ҳали йўқ</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
