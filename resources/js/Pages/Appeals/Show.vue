<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import { useFormatters } from '@/Composables/useFormatters';
import {
    APPEAL_STATUS_LABELS, APPEAL_STATUS_COLORS,
    APPEAL_PRIORITY_LABELS, APPEAL_PRIORITY_COLORS,
    APPEAL_SOURCE_LABELS,
    DECISION_TYPE_LABELS, DECISION_TYPE_COLORS,
} from '@/Constants/labels';
import type { CitizenAppeal } from '@/types';

const props = defineProps<{
    appeal: CitizenAppeal;
    active_assignee: { type: string; name: string; id: number } | null;
}>();

const { success } = useFlash();
const { formatDateShort, formatDateTime } = useFormatters();

const isOverdue = computed(() => {
    if (!props.appeal.sla_due_at) return false;
    if (['completed', 'closed'].includes(props.appeal.status)) return false;
    return new Date(props.appeal.sla_due_at) < new Date();
});

// Qaror chiqarish formasi
const showDecisionForm = ref(false);
const decisionForm = ref({
    council_id: props.appeal.active_assignment?.assignee_type === 'council' ? props.appeal.active_assignment.assignee_id : null,
    meeting_date: new Date().toISOString().split('T')[0],
    decision_type: 'approve' as 'approve' | 'reject' | 'partial' | 'escalate' | 'info',
    decision_text: '',
});

function submitDecision() {
    router.post(`/appeals/${props.appeal.id}/decisions`, decisionForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showDecisionForm.value = false;
            decisionForm.value.decision_text = '';
        },
    });
}

function deleteAppeal() {
    if (confirm('Мурожаатни ўчиришга ишончингиз комилми?')) {
        router.delete(`/appeals/${props.appeal.id}`);
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <!-- Sarlavha + actions -->
        <div class="mb-6 flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900">{{ appeal.applicant_name }}</h1>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="APPEAL_STATUS_COLORS[appeal.status]">
                        {{ APPEAL_STATUS_LABELS[appeal.status] }}
                    </span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="APPEAL_PRIORITY_COLORS[appeal.priority]">
                        {{ APPEAL_PRIORITY_LABELS[appeal.priority] }}
                    </span>
                    <span v-if="isOverdue" class="rounded-full bg-red-100 text-red-700 px-2.5 py-0.5 text-xs font-bold">
                        ⚠ Муддат ўтган
                    </span>
                </div>
                <p class="text-sm text-gray-500">
                    {{ appeal.category?.name_cyr }}
                    <span v-if="appeal.sub_category"> → {{ appeal.sub_category.name_cyr }}</span>
                    · {{ APPEAL_SOURCE_LABELS[appeal.source] }}
                </p>
            </div>
            <div class="flex gap-2">
                <Link :href="`/appeals/${appeal.id}/edit`"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Таҳрирлаш</Link>
                <button @click="deleteAppeal"
                    class="rounded-md border border-red-300 bg-white px-4 py-2 text-sm text-red-600 hover:bg-red-50">Ўчириш</button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Chap: asosiy ma'lumotlar -->
            <div class="lg:col-span-2 space-y-5">
                <!-- Murojaat matni -->
                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="font-semibold text-gray-900 mb-3">Мурожаат матни</h2>
                    <p class="whitespace-pre-wrap text-sm text-gray-700">{{ appeal.body }}</p>
                </div>

                <!-- Qarorlar -->
                <div class="rounded-lg bg-white p-5 shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-900">Қарорлар ({{ appeal.decisions?.length ?? 0 }})</h2>
                        <button v-if="['routed', 'in_review', 'reopened'].includes(appeal.status)"
                            @click="showDecisionForm = !showDecisionForm"
                            class="rounded-md bg-blue-600 px-3 py-1.5 text-xs text-white hover:bg-blue-700">
                            + Қарор қайд этиш
                        </button>
                    </div>

                    <!-- Yangi qaror form -->
                    <div v-if="showDecisionForm" class="mb-4 rounded-md border border-blue-200 bg-blue-50 p-4 space-y-3">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-xs font-medium text-gray-700">Йиғилиш санаси</label>
                                <input v-model="decisionForm.meeting_date" type="date"
                                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-700">Қарор тури</label>
                                <select v-model="decisionForm.decision_type"
                                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                    <option v-for="(label, key) in DECISION_TYPE_LABELS" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                        </div>
                        <textarea v-model="decisionForm.decision_text" rows="3"
                            placeholder="Қарор матни..."
                            class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                        <div class="flex justify-end gap-2">
                            <button @click="showDecisionForm = false"
                                class="rounded border border-gray-300 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50">Бекор</button>
                            <button @click="submitDecision" :disabled="!decisionForm.decision_text"
                                class="rounded bg-blue-600 px-3 py-1.5 text-xs text-white hover:bg-blue-700 disabled:opacity-50">Сақлаш</button>
                        </div>
                    </div>

                    <!-- Qarorlar ro'yxati -->
                    <div v-if="appeal.decisions?.length" class="space-y-3">
                        <div v-for="d in appeal.decisions" :key="d.id" class="rounded-md border border-gray-200 p-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="DECISION_TYPE_COLORS[d.decision_type]">
                                    {{ DECISION_TYPE_LABELS[d.decision_type] }}
                                </span>
                                <span class="text-xs text-gray-400">{{ formatDateTime(d.decided_at) }}</span>
                            </div>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ d.decision_text }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ d.council?.mahalla?.name_cyr }} · {{ d.decider?.name }}
                            </p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400">Ҳозирча қарор қабул қилинмаган</p>
                </div>

                <!-- Tarix -->
                <div v-if="appeal.status_history?.length" class="rounded-lg bg-white p-5 shadow">
                    <h2 class="font-semibold text-gray-900 mb-3">Ҳолат ўзгаришлари</h2>
                    <div class="space-y-2">
                        <div v-for="h in appeal.status_history" :key="h.id" class="border-l-2 border-blue-200 pl-3 py-1">
                            <p class="text-sm">
                                <span v-if="h.from_status" class="text-gray-400">{{ APPEAL_STATUS_LABELS[h.from_status] }} →</span>
                                <span class="font-medium">{{ APPEAL_STATUS_LABELS[h.to_status] }}</span>
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ formatDateTime(h.changed_at) }} · {{ h.changer?.name ?? 'Тизим' }}
                                <span v-if="h.reason"> · {{ h.reason }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- O'ng: ma'lumotlar -->
            <div class="space-y-5">
                <div class="rounded-lg bg-white p-5 shadow space-y-2 text-sm">
                    <h3 class="font-semibold text-gray-900 mb-2">Маълумотлар</h3>
                    <div><span class="text-gray-500">Телефон:</span> {{ appeal.applicant_phone ?? '—' }}</div>
                    <div><span class="text-gray-500">Манзил:</span> {{ appeal.applicant_address ?? '—' }}</div>
                    <div><span class="text-gray-500">Маҳалла:</span> {{ appeal.mahalla?.name_cyr ?? '—' }}</div>
                    <div v-if="appeal.amount"><span class="text-gray-500">Сумма/Гектар:</span> {{ appeal.amount }}</div>
                    <div><span class="text-gray-500">Юборилди:</span> {{ appeal.submitted_at ? formatDateTime(appeal.submitted_at) : '—' }}</div>
                    <div v-if="appeal.sla_due_at">
                        <span class="text-gray-500">Муддат:</span>
                        <span :class="isOverdue ? 'text-red-600 font-semibold' : ''">{{ formatDateShort(appeal.sla_due_at) }}</span>
                    </div>
                </div>

                <div v-if="active_assignee" class="rounded-lg bg-blue-50 border border-blue-200 p-5">
                    <p class="text-xs text-blue-700 font-semibold mb-1">МАСЪУЛ</p>
                    <p class="font-medium text-blue-900">{{ active_assignee.name }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
