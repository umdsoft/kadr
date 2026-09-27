<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFlash } from '@/Composables/useFlash';

const props = defineProps<{
    task: Record<string, any>;
    canReport: boolean;
    canRemoveFromControl: boolean;
    canReview: boolean;
}>();

const { success } = useFlash();

const statusLabels: Record<string, string> = {
    not_started: 'Бажарилмаган', in_progress: 'Бажарилмоқда', completed: 'Бажарилган', overdue: 'Муддати ўтган',
};
const statusColors: Record<string, string> = {
    not_started: 'bg-gray-100 text-gray-600', in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700', overdue: 'bg-red-100 text-red-700',
};
const reviewLabels: Record<string, string> = {
    submitted: 'Кўриб чиқилмоқда', approved: 'Тасдиқланган', returned: 'Қайтарилган',
};
const reviewColors: Record<string, string> = {
    submitted: 'bg-amber-100 text-amber-800', approved: 'bg-green-100 text-green-700', returned: 'bg-orange-100 text-orange-700',
};
const tlColor: Record<string, string> = {
    gray: 'bg-slate-400', blue: 'bg-blue-500', amber: 'bg-amber-500',
    green: 'bg-green-500', orange: 'bg-orange-500', purple: 'bg-purple-500',
};
const tlBadge: Record<string, string> = {
    gray: 'bg-slate-100 text-slate-600', blue: 'bg-blue-100 text-blue-700', amber: 'bg-amber-100 text-amber-800',
    green: 'bg-green-100 text-green-700', orange: 'bg-orange-100 text-orange-700', purple: 'bg-purple-100 text-purple-700',
};

const codeLabel = props.task.plan_document_number || props.task.item_number || 'Топшириқ';

// Javob berish modali
const showResponseModal = ref(false);

// Javob berish (matn + bir nechta fayl) — yuborilgach "Ko'rib chiqilmoqda" holatiga o'tadi
const respondForm = useForm<{ execution_report: string; files: File[] }>({ execution_report: '', files: [] });
function onFile(e: Event) {
    const input = e.target as HTMLInputElement;
    const picked = Array.from(input.files ?? []);
    respondForm.files = [...respondForm.files, ...picked];
    input.value = ''; // bir xil faylni qayta tanlash mumkin bo'lishi uchun
}
function removeFile(i: number) {
    respondForm.files = respondForm.files.filter((_, idx) => idx !== i);
}
function fileSize(bytes: number) {
    return bytes < 1024 * 1024 ? `${Math.round(bytes / 1024)} KB` : `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}
function submitResponse() {
    respondForm.post(`/topshiriqlar/${props.task.id}/respond`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { showResponseModal.value = false; respondForm.reset(); },
    });
}
function deleteDoc(id: string) {
    if (confirm('Ҳужжатни ўчирасизми?')) {
        router.delete(`/documents/${id}`, { preserveScroll: true });
    }
}

// Tasdiqlash / qaytarish
const showReturn = ref(false);
const returnForm = useForm({ comment: '' });
function approve() {
    if (confirm('Ижрони тасдиқлайсизми?')) {
        router.put(`/topshiriqlar/${props.task.id}/approve`, {}, { preserveScroll: true });
    }
}
function returnForRework() {
    returnForm.put(`/topshiriqlar/${props.task.id}/return`, {
        preserveScroll: true, onSuccess: () => { showReturn.value = false; returnForm.reset(); },
    });
}

// Nazoratdan yechish
const showRemove = ref(false);
const removeForm = useForm({ reason: '' });
function removeControl() {
    removeForm.put(`/topshiriqlar/${props.task.id}/remove-control`, {
        preserveScroll: true, onSuccess: () => { showRemove.value = false; },
    });
}
function restoreControl() {
    if (confirm('Топшириқни қайта назоратга қайтарасизми?')) {
        router.put(`/topshiriqlar/${props.task.id}/restore-control`, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout>
        <div v-if="success" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ success }}</div>

        <Link href="/topshiriqlar" class="mb-3 inline-block text-sm text-gray-400 hover:text-gray-600">← Топшириқлар</Link>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <!-- Sarlavha (hujjat) -->
            <div class="border-b border-slate-100 p-6">
                <div class="mb-3 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-400">★</span>
                        <span class="font-bold text-slate-900">{{ codeLabel }}</span>
                        <span v-if="task.plan_document_date" class="text-xs text-slate-400">{{ task.plan_document_date }}</span>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <button v-if="canReport && task.under_control" @click="showResponseModal = true"
                            class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                            </svg>
                            Жавоб бериш
                        </button>
                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="task.source === 'control_plan' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'">
                                {{ task.source === 'control_plan' ? 'Назорат режа' : 'Мустақил' }}
                            </span>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="task.under_control ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600'">
                                {{ task.under_control ? '● Назоратда' : '✓ Ечилган' }}
                            </span>
                            <span v-if="task.review_status" class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="reviewColors[task.review_status]">
                                {{ reviewLabels[task.review_status] }}
                            </span>
                        </div>
                    </div>
                </div>

                <h1 class="text-center text-lg font-bold leading-snug text-slate-900">{{ task.plan_title ?? task.title }}</h1>
            </div>

            <!-- ===== TOPSHIRIQ MA'LUMOTLARI ===== -->
            <div class="space-y-6 p-6">
                <div v-if="task.title && task.title !== task.plan_title">
                    <p class="text-xs font-medium text-slate-400">Қисқача мазмуни</p>
                    <p class="mt-1 text-sm text-slate-700">{{ task.title }}</p>
                </div>

                <!-- Meta grid -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium text-slate-400">Бажариш муддати</p>
                        <p class="mt-0.5 text-sm font-semibold" :class="!task.under_control ? 'text-slate-500' : 'text-red-600'">{{ task.deadline ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400">Топшириқ келиб тушган сана</p>
                        <p class="mt-0.5 text-sm text-slate-700">{{ task.created_at ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400">Манба</p>
                        <p class="mt-0.5 text-sm text-slate-700">{{ task.source === 'control_plan' ? 'Назорат режа' : 'Мустақил топшириқ' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400">Комплекс</p>
                        <p class="mt-0.5 text-sm text-slate-700">{{ task.kompleks ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400">Берган</p>
                        <p class="mt-0.5 text-sm text-slate-700">{{ task.creator ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400">Ҳолат</p>
                        <span class="mt-0.5 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusColors[task.execution_status]">
                            {{ statusLabels[task.execution_status] ?? task.execution_status }}
                        </span>
                    </div>
                </div>

                <!-- Biriktirilgan fayllar (faqat ko'rish — fayllar topshiriq/javob bilan biriktiriladi) -->
                <div>
                    <p class="mb-2 text-xs font-medium text-slate-400">Бириктирилган файллар</p>
                    <div class="flex flex-wrap gap-2">
                        <div v-for="d in (task.documents ?? [])" :key="d.id"
                            class="flex w-80 items-center gap-3 rounded-lg border border-slate-200 bg-white p-2.5 transition hover:border-blue-300 hover:shadow-sm">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50">
                                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-slate-800">{{ d.original_name }}</p>
                                <p class="text-[10px] text-slate-400">{{ d.uploader }} · {{ d.created_at }}</p>
                            </div>
                            <a :href="`/documents/${d.id}/download`" target="_blank"
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-blue-600" title="Кўриш">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </a>
                            <a :href="`/documents/${d.id}/download`"
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-blue-600" title="Юклаб олиш">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                            </a>
                            <button v-if="canReport && task.under_control" @click="deleteDoc(d.id)"
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-400 hover:bg-red-50 hover:text-red-600" title="Ўчириш">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="!(task.documents ?? []).length" class="text-sm text-slate-400">—</p>
                    </div>
                </div>

                <!-- Havola (link) -->
                <div v-if="task.link">
                    <p class="mb-1 text-xs font-medium text-slate-400">Ҳавола</p>
                    <a :href="task.link" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1.5 break-all text-sm text-blue-600 hover:underline">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                        </svg>
                        {{ task.link }}
                    </a>
                </div>

                <!-- Band raqami va topshiriq mazmuni -->
                <div>
                    <p class="mb-2 text-xs font-medium text-slate-400">Банд рақами ва топшириқ мазмуни</p>
                    <div class="rounded-lg bg-amber-50 p-4 text-sm text-slate-800">
                        <span v-if="task.item_number" class="font-bold">{{ task.item_number }}. </span>{{ task.task_description }}
                    </div>
                </div>

                <!-- Amalga oshirish mexanizmi -->
                <div v-if="task.implementation">
                    <p class="mb-2 text-xs font-medium text-slate-400">Амалга ошириш механизми</p>
                    <div class="rounded-lg bg-amber-50 p-4 text-sm text-slate-800">{{ task.implementation }}</div>
                </div>

                <div v-if="task.funding_source">
                    <p class="text-xs font-medium text-slate-400">Молиялаштириш манбаси</p>
                    <p class="mt-0.5 text-sm text-slate-700">{{ task.funding_source }}</p>
                </div>

                <!-- Ijrochilar ro'yxati -->
                <div>
                    <p class="mb-2 text-xs font-medium text-slate-400">Ижрочилар рўйхати ({{ (task.responsibles ?? []).length }})</p>
                    <div class="space-y-1.5">
                        <div v-for="(r, i) in (task.responsibles ?? [])" :key="i"
                            class="flex items-center gap-3 rounded-lg border px-3 py-2"
                            :class="r.is_primary ? 'border-amber-300 bg-amber-50' : 'border-slate-200'">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                                :class="r.type === 'organization' ? 'bg-violet-100 text-violet-700' : 'bg-blue-100 text-blue-700'">
                                {{ (r.name || '?').charAt(0) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-1.5 truncate text-sm font-medium text-slate-900">
                                    <span v-if="r.is_primary" class="text-amber-500">★</span>{{ r.name }}
                                    <span v-if="r.is_primary" class="rounded bg-amber-200 px-1.5 text-[10px] text-amber-800">Асосий ижрочи</span>
                                    <span v-if="r.type === 'organization'" class="rounded bg-violet-100 px-1.5 text-[10px] text-violet-700">ташкилот</span>
                                </p>
                                <p v-if="r.position" class="truncate text-xs text-slate-400">{{ r.position }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== BERILGAN JAVOBLAR (bir sahifada) ===== -->
            <div class="border-t border-slate-100 p-6">
                <!-- ===== Amallar paneli ===== -->
                <!-- 1-bosqich: tashkilot javob yubordi → ko'rib chiqish (tasdiqlash/qaytarish) -->
                <div v-if="canReview && task.review_status === 'submitted'" class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="mb-1 text-sm font-semibold text-amber-900">Ташкилот жавоб юборди — кўриб чиқинг</p>
                    <p class="mb-3 text-xs text-amber-700">
                        <b>Тасдиқлаш</b> — жавобни қабул қилади (топшириқ ҳали назоратда қолади).
                        <b>Қайтариш</b> — қайта ишлашга юборади.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <button @click="approve" class="rounded-md bg-green-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-green-700">✓ Жавобни тасдиқлаш</button>
                        <button @click="showReturn = !showReturn" class="rounded-md border border-orange-300 bg-white px-4 py-1.5 text-sm font-medium text-orange-700 hover:bg-orange-50">↩ Қайтариш</button>
                    </div>
                    <div v-if="showReturn" class="mt-3">
                        <textarea v-model="returnForm.comment" rows="2" placeholder="Қайтариш сабаби"
                            class="w-full rounded-md border border-orange-300 px-3 py-2 text-sm"></textarea>
                        <button @click="returnForRework" :disabled="returnForm.processing"
                            class="mt-2 rounded-md bg-orange-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-orange-700 disabled:opacity-50">Қайтаришни тасдиқлаш</button>
                    </div>
                </div>

                <!-- Tasdiqlangan banner -->
                <div v-else-if="task.review_status === 'approved' && task.under_control" class="mb-5 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                    <span>✓</span><span>Жавоб тасдиқланган<span v-if="task.reviewed_at"> — {{ task.reviewed_at }}</span>. Энди топшириқни назоратдан ечишингиз мумкин.</span>
                </div>

                <!-- Qaytarilgan banner -->
                <div v-else-if="task.review_status === 'returned'" class="mb-5 rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-800">
                    ↩ Қайта ишлашга қайтарилган<span v-if="task.review_comment">: {{ task.review_comment }}</span> — ташкилот қайта жавоб юбориши кутилмоқда.
                </div>

                <!-- 2-bosqich: nazoratdan yechish (yakuniy) — javob ko'rib chiqilgandan keyin -->
                <div v-if="canRemoveFromControl && task.under_control && task.review_status !== 'submitted'" class="mb-5">
                    <button @click="showRemove = !showRemove"
                        class="rounded-md border border-amber-300 bg-amber-50 px-4 py-1.5 text-sm font-medium text-amber-700 hover:bg-amber-100">Назоратдан ечиш</button>
                    <p class="mt-1 text-xs text-slate-400">Топшириқ якунланди — назорат рўйхатидан олиб ташлайди (якуний).</p>
                    <div v-if="showRemove" class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <textarea v-model="removeForm.reason" rows="2" placeholder="Изоҳ (ихтиёрий)"
                            class="w-full rounded-md border border-amber-300 px-3 py-2 text-sm"></textarea>
                        <button @click="removeControl" :disabled="removeForm.processing"
                            class="mt-2 rounded-md bg-amber-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-50">Назоратдан ечишни тасдиқлаш</button>
                    </div>
                </div>

                <!-- Nazoratdan yechilgan → qayta nazoratga -->
                <div v-if="canRemoveFromControl && !task.under_control" class="mb-5 flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm">
                    <span class="text-gray-600">Топшириқ назоратдан ечилган<span v-if="task.removed_at"> — {{ task.removed_at }}</span>.</span>
                    <button @click="restoreControl" class="rounded-md border border-gray-300 bg-white px-3 py-1 text-xs text-gray-600 hover:bg-gray-100">Қайта назоратга</button>
                </div>

                <!-- Berilgan javoblar (timeline) -->
                <h3 class="mb-4 text-base font-bold text-slate-900">Берилган жавоблар</h3>
                <div class="space-y-6">
                    <div v-for="(e, i) in (task.timeline ?? [])" :key="i" class="border-b border-slate-100 pb-6 last:border-0 last:pb-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-block rounded-md px-3 py-1.5 text-xs font-semibold" :class="tlBadge[e.color] ?? 'bg-slate-100 text-slate-600'">{{ e.label }}</span>
                                <p class="mt-2 text-xs text-slate-400">{{ e.date }}</p>
                            </div>
                            <div class="max-w-[55%] text-right">
                                <p class="text-sm font-semibold text-slate-800">{{ e.author }}</p>
                                <p v-if="e.org" class="text-xs text-slate-400">{{ e.org }}</p>
                            </div>
                        </div>

                        <p v-if="e.text" class="mt-3 whitespace-pre-wrap border-l-2 border-slate-200 pl-3 text-sm leading-relaxed text-slate-600">{{ e.text }}</p>

                        <!-- Javobga biriktirilgan fayllar -->
                        <div v-if="e.documents?.length" class="mt-3 flex flex-wrap gap-2">
                            <div v-for="d in e.documents" :key="d.id"
                                class="flex w-72 items-center gap-3 rounded-lg border border-slate-200 bg-white p-2.5">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-500 text-[10px] font-bold text-white">PDF</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-medium text-slate-800">{{ d.original_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ fileSize(d.file_size) }} · {{ d.created_at }}</p>
                                </div>
                                <a :href="`/documents/${d.id}/download`" target="_blank"
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-blue-600" title="Кўриш">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <p v-if="!(task.timeline ?? []).length" class="text-sm text-slate-400">Ҳали жавоб берилмаган</p>
                </div>
            </div>
        </div>

        <!-- ===== JAVOB BERISH MODALI ===== -->
        <div v-if="showResponseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showResponseModal = false">
            <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Жавоб бериш</h3>
                    <button @click="showResponseModal = false" class="text-xl leading-none text-slate-400 hover:text-slate-600">×</button>
                </div>
                <form @submit.prevent="submitResponse" class="space-y-4 p-5">
                    <div class="rounded-md bg-amber-50 p-2.5 text-xs text-slate-600">{{ task.task_description }}</div>

                    <!-- Fayl biriktirish (asosiy, bir nechta) -->
                    <div>
                        <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-slate-600">
                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                            </svg>
                            Ижро файлларини бириктириш
                            <span v-if="respondForm.files.length" class="rounded-full bg-blue-100 px-1.5 text-[10px] text-blue-700">{{ respondForm.files.length }}</span>
                        </label>

                        <!-- Tanlangan fayllar ro'yxati -->
                        <div v-if="respondForm.files.length" class="mb-2 space-y-1.5">
                            <div v-for="(f, i) in respondForm.files" :key="i"
                                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2">
                                <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <span class="min-w-0 flex-1 truncate text-xs text-slate-700">{{ f.name }}</span>
                                <span class="shrink-0 text-[10px] text-slate-400">{{ fileSize(f.size) }}</span>
                                <button type="button" @click="removeFile(i)" class="shrink-0 text-slate-400 hover:text-red-600" title="Олиб ташлаш">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Qo'shish maydoni -->
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 px-4 py-4 text-sm text-slate-500 transition hover:border-blue-300 hover:bg-slate-50">
                            <input type="file" multiple @change="onFile" class="hidden" />
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <span>{{ respondForm.files.length ? 'Яна файл қўшиш' : 'Файл(лар) танлаш учун босинг' }}</span>
                        </label>
                        <span v-if="respondForm.errors.files" class="mt-1 block text-xs text-red-600">{{ respondForm.errors.files }}</span>
                        <span v-if="respondForm.errors['files.0']" class="mt-1 block text-xs text-red-600">{{ respondForm.errors['files.0'] }}</span>
                    </div>

                    <!-- Izoh (ixtiyoriy) -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Изоҳ (ихтиёрий)</label>
                        <textarea v-model="respondForm.execution_report" rows="3" placeholder="Қисқача изоҳ ёзишингиз мумкин"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                        <span v-if="respondForm.errors.execution_report" class="mt-1 block text-xs text-red-600">{{ respondForm.errors.execution_report }}</span>
                    </div>

                    <button type="submit" :disabled="respondForm.processing || (!respondForm.files.length && !respondForm.execution_report)"
                        class="w-full rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                        {{ respondForm.processing ? 'Юборилмоқда...' : 'Жавобни юбориш' }}
                    </button>
                    <p class="text-center text-[11px] text-slate-400">Жавоб юборилгач топшириқ «Кўриб чиқилмоқда» ҳолатига ўтади.</p>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

