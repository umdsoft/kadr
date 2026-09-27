<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import FormField from '@/Components/UI/FormField.vue';
import { EXECUTION_STATUS_LABELS } from '@/Constants/labels';

interface Responsible {
    assignee_type: 'user' | 'organization';
    assignee_id: string | null;
    user_id: string | null;
    responsible_name: string;
    responsible_position: string;
    is_primary: boolean;
}
export interface PlanItem {
    id?: string;
    item_number: string;
    task_description: string;
    implementation: string;
    funding_source: string;
    deadline: string;
    execution_status?: string;
    execution_report?: string;
    responsibles: Responsible[];
    addType: 'user' | 'organization'; // UI: keyingi masъul turi
}

interface Props {
    users: { id: string; name: string }[];
    organizations?: { id: string; name_cyr: string }[];
    plan?: any;          // Edit holatida mavjud plan
    submitUrl: string;   // POST yoki PUT URL
    method: 'post' | 'put';
    submitLabel: string;
    showStatus?: boolean; // Edit holatida holat va hisobot ko'rinadi
}
const props = withDefaults(defineProps<Props>(), { showStatus: false, organizations: () => [] });

// Plan boshlang'ich qiymatlari
const initialItems: PlanItem[] = (props.plan?.items ?? []).map((item: any) => ({
    id: item.id,
    item_number: item.item_number ?? '',
    task_description: item.task_description ?? '',
    implementation: item.implementation ?? '',
    funding_source: item.funding_source ?? '',
    deadline: item.deadline ? String(item.deadline).split('T')[0] : '',
    execution_status: item.execution_status ?? 'not_started',
    execution_report: item.execution_report ?? '',
    addType: 'user',
    responsibles: (item.responsibles ?? []).map((r: any) => ({
        assignee_type: r.assignee_type ?? 'user',
        assignee_id: r.assignee_id ?? r.user_id ?? null,
        user_id: r.user_id ?? null,
        responsible_name: r.responsible_name ?? '',
        responsible_position: r.responsible_position ?? '',
        is_primary: !!r.is_primary,
    })),
}));

const items = ref<PlanItem[]>(initialItems);

const form = useForm({
    title: props.plan?.title ?? '',
    document_number: props.plan?.document_number ?? '',
    document_date: props.plan?.document_date ? String(props.plan.document_date).split('T')[0] : '',
    status: props.plan?.status ?? 'active',
    status_date: props.plan?.status_date ?? '',
});

function addItem() {
    items.value.push({
        item_number: `${items.value.length + 1}`,
        task_description: '',
        implementation: '',
        funding_source: '',
        deadline: '',
        execution_status: 'not_started',
        execution_report: '',
        addType: 'user',
        responsibles: [],
    });
}

function removeItem(i: number) {
    items.value.splice(i, 1);
}

function setAddType(i: number, type: 'user' | 'organization') {
    items.value[i].addType = type;
}

/** Joriy addType uchun hali qo'shilmagan ijrochilar ro'yxati. */
function availableFor(item: PlanItem): { id: string; label: string }[] {
    const chosen = new Set(
        item.responsibles
            .filter(r => r.assignee_type === item.addType)
            .map(r => r.assignee_id),
    );

    if (item.addType === 'organization') {
        return props.organizations.filter(o => !chosen.has(o.id)).map(o => ({ id: o.id, label: o.name_cyr }));
    }

    return props.users.filter(u => !chosen.has(u.id)).map(u => ({ id: u.id, label: u.name }));
}

/** Tanlovdan ijrochini ro'yxatga qo'shadi (pastga tushadi) va selectni qayta tiklaydi. */
function addResponsibleFromSelect(i: number, event: Event) {
    const select = event.target as HTMLSelectElement;
    const id = select.value || null; // UUID — string sifatida
    if (!id) {
        return;
    }

    const item = items.value[i];
    const type = item.addType;
    const name = type === 'organization'
        ? (props.organizations.find(o => o.id === id)?.name_cyr ?? '')
        : (props.users.find(u => u.id === id)?.name ?? '');

    item.responsibles.push({
        assignee_type: type,
        assignee_id: id,
        user_id: type === 'user' ? id : null,
        responsible_name: name,
        responsible_position: '',
        is_primary: item.responsibles.length === 0, // birinchi qo'shilgan — asosiy
    });

    select.value = ''; // keyingi tanlov uchun reset
}

/** Asosiy ijrochini belgilash — faqat bittasi asosiy bo'ladi. */
function setPrimary(i: number, ri: number) {
    items.value[i].responsibles.forEach((r, idx) => {
        r.is_primary = idx === ri;
    });
}

function removeResponsible(i: number, ri: number) {
    const list = items.value[i].responsibles;
    const wasPrimary = list[ri]?.is_primary;
    list.splice(ri, 1);
    // Asosiy o'chirilsa va boshqalar qolsa — birinchisini asosiy qilamiz
    if (wasPrimary && list.length && ! list.some(r => r.is_primary)) {
        list[0].is_primary = true;
    }
}

function submit() {
    // Бўш масъул қаторларини (assignee танланмаган) сақламаймиз
    const cleaned = items.value.map((it) => ({
        ...it,
        responsibles: it.responsibles.filter((r) => r.assignee_id),
    }));
    const data = form.transform((d) => ({ ...d, items: cleaned }));
    if (props.method === 'put') {
        data.put(props.submitUrl);
    } else {
        data.post(props.submitUrl);
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Ҳужжат маълумотлари -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Ҳужжат маълумотлари</h2>
            <div class="space-y-4">
                <FormField label="Тўлиқ расмий сарлавҳа" required :error="form.errors.title">
                    <template #default="{ hasError }">
                        <textarea v-model="form.title" rows="3"
                            class="w-full rounded-md border px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            :class="hasError ? 'border-red-500' : 'border-gray-300'" />
                    </template>
                </FormField>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <FormField label="Ҳужжат рақами">
                        <input v-model="form.document_number" type="text" placeholder="ПҚ-89"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </FormField>
                    <FormField label="Ҳужжат санаси">
                        <input v-model="form.document_date" type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </FormField>
                    <FormField label="Ҳолат санаси">
                        <input v-model="form.status_date" type="text" placeholder="2026 йил 1 апрел ҳолатига"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </FormField>
                </div>

                <FormField v-if="showStatus" label="Режа ҳолати">
                    <select v-model="form.status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="active">Фаол</option>
                        <option value="completed">Бажарилган</option>
                        <option value="archived">Архив</option>
                    </select>
                </FormField>
            </div>
        </div>

        <!-- Бандлар -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Бандлар (топшириқлар)</h2>
                <button type="button" @click="addItem"
                    class="rounded-md bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">+ Банд қўшиш</button>
            </div>

            <div v-for="(item, i) in items" :key="i" class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">{{ i + 1 }}-банд</span>
                    <button type="button" @click="removeItem(i)" class="text-sm text-red-600 hover:text-red-800">Ўчириш</button>
                </div>

                <div class="space-y-3">
                    <FormField label="Банд рақами" required>
                        <input v-model="item.item_number" type="text" placeholder="1 а)-банд"
                            class="w-40 rounded border border-gray-300 px-2 py-1.5 text-sm" />
                    </FormField>

                    <FormField label="Чора-тадбир номи" required>
                        <textarea v-model="item.task_description" rows="3"
                            class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                    </FormField>

                    <FormField label="Амалга оширилиш механизми">
                        <textarea v-model="item.implementation" rows="2"
                            class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                    </FormField>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <FormField label="Молиялаштириш манбаси">
                            <input v-model="item.funding_source" type="text"
                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                        </FormField>
                        <FormField label="Ижро муддати">
                            <input v-model="item.deadline" type="date"
                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                        </FormField>
                    </div>

                    <!-- Edit holatida bajarilish ham -->
                    <div v-if="showStatus" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <FormField label="Бажарилиш ҳолати">
                            <select v-model="item.execution_status" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                <option v-for="(label, key) in EXECUTION_STATUS_LABELS" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </FormField>
                        <FormField label="Бажарилиши ҳақида">
                            <input v-model="item.execution_report" type="text"
                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                        </FormField>
                    </div>

                    <!-- Масъуллар -->
                    <div class="mt-2 rounded border border-blue-100 bg-blue-50 p-3">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-blue-800">Масъул шахслар</span>
                            <!-- Тур танлаш: кейинги масъул ким бўлади -->
                            <div class="inline-flex rounded-md border border-blue-200 bg-white p-0.5">
                                <button type="button" @click="setAddType(i, 'user')"
                                    :class="item.addType === 'user' ? 'bg-blue-600 text-white' : 'text-gray-600'"
                                    class="rounded px-2.5 py-0.5 text-xs font-medium transition">Ҳокимлик ходими</button>
                                <button type="button" @click="setAddType(i, 'organization')"
                                    :class="item.addType === 'organization' ? 'bg-blue-600 text-white' : 'text-gray-600'"
                                    class="rounded px-2.5 py-0.5 text-xs font-medium transition">Ташкилот</button>
                            </div>
                        </div>

                        <!-- Танлаб қўшиш — танлангач пастдаги рўйхатга тушади -->
                        <select @change="addResponsibleFromSelect(i, $event)"
                            class="mb-2 w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                            <option value="">+ {{ item.addType === 'organization' ? 'Ташкилот' : 'Ходим' }} танлаб қўшинг...</option>
                            <option v-for="a in availableFor(item)" :key="a.id" :value="a.id">{{ a.label }}</option>
                        </select>

                        <!-- Танланганлар рўйхати (карта) — ★ асосий ижрочи -->
                        <div v-for="(r, ri) in item.responsibles" :key="ri"
                            class="mb-1.5 flex items-center gap-2.5 rounded-md border px-3 py-2 transition"
                            :class="r.is_primary ? 'border-amber-300 bg-amber-50 ring-1 ring-amber-200' : 'border-gray-200 bg-white'">
                            <!-- Асосий ижрочи юлдузчаси -->
                            <button type="button" @click="setPrimary(i, ri)"
                                :title="r.is_primary ? 'Асосий ижрочи' : 'Асосий қилиб белгилаш'" class="shrink-0">
                                <svg class="h-5 w-5 transition" :class="r.is_primary ? 'text-amber-500' : 'text-gray-300 hover:text-amber-400'"
                                    :fill="r.is_primary ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                            </button>
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                                :class="r.assignee_type === 'organization' ? 'bg-violet-100 text-violet-700' : 'bg-blue-100 text-blue-700'">
                                {{ (r.responsible_name || '?').charAt(0) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-1.5 truncate text-sm font-medium text-gray-900">
                                    {{ r.responsible_name }}
                                    <span v-if="r.is_primary"
                                        class="rounded bg-amber-200 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800">Асосий ижрочи</span>
                                    <span v-if="r.assignee_type === 'organization'"
                                        class="rounded bg-violet-100 px-1.5 py-0.5 text-[10px] text-violet-700">ташкилот</span>
                                </p>
                                <input v-model="r.responsible_position" type="text" placeholder="Лавозими (ихтиёрий)"
                                    class="mt-0.5 w-full border-0 bg-transparent p-0 text-xs text-gray-500 focus:ring-0" />
                            </div>
                            <button type="button" @click="removeResponsible(i, ri)"
                                class="shrink-0 text-red-500 hover:text-red-700" title="Ўчириш">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="item.responsibles.length" class="mt-1 text-[11px] text-gray-400">★ — асосий ижрочини белгилаш учун юлдузчани босинг</p>
                        <p v-else class="text-xs text-blue-600">Масъул шахслар ҳали қўшилмаган</p>
                    </div>
                </div>
            </div>

            <div v-if="items.length === 0" class="rounded-md border-2 border-dashed border-gray-300 py-8 text-center text-sm text-gray-500">
                Бандлар ҳали қўшилмаган. «Банд қўшиш» тугмасини босинг.
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <Link href="/control-plans" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Бекор қилиш</Link>
            <button type="submit" :disabled="form.processing"
                class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>
