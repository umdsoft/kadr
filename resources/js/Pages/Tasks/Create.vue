<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Responsible {
    assignee_type: 'user' | 'organization';
    assignee_id: string | null;
    responsible_name: string;
    responsible_position: string;
    is_primary: boolean;
}

const props = defineProps<{
    organizations: { id: string; name_cyr: string }[];
    users: { id: string; name: string }[];
}>();

const form = useForm<{
    title: string;
    task_description: string;
    implementation: string;
    deadline: string;
    link: string;
    files: File[];
    responsibles: Responsible[];
}>({
    title: '',
    task_description: '',
    implementation: '',
    deadline: '',
    link: '',
    files: [],
    responsibles: [],
});

const addType = ref<'user' | 'organization'>('organization');

// Asosiy hujjat(lar)
function onFile(e: Event) {
    const input = e.target as HTMLInputElement;
    form.files = [...form.files, ...Array.from(input.files ?? [])];
    input.value = '';
}
function removeFile(i: number) {
    form.files = form.files.filter((_, idx) => idx !== i);
}
function fileSize(bytes: number) {
    return bytes < 1024 * 1024 ? `${Math.round(bytes / 1024)} KB` : `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

/** Joriy addType uchun hali qo'shilmagan ijrochilar. */
function availableList(): { id: string; label: string }[] {
    const chosen = new Set(
        form.responsibles.filter(r => r.assignee_type === addType.value).map(r => r.assignee_id),
    );
    if (addType.value === 'organization') {
        return props.organizations.filter(o => !chosen.has(o.id)).map(o => ({ id: o.id, label: o.name_cyr }));
    }
    return props.users.filter(u => !chosen.has(u.id)).map(u => ({ id: u.id, label: u.name }));
}

function addFromSelect(event: Event) {
    const select = event.target as HTMLSelectElement;
    const id = select.value || null;
    if (!id) return;

    const type = addType.value;
    const name = type === 'organization'
        ? (props.organizations.find(o => o.id === id)?.name_cyr ?? '')
        : (props.users.find(u => u.id === id)?.name ?? '');

    form.responsibles.push({
        assignee_type: type,
        assignee_id: id,
        responsible_name: name,
        responsible_position: '',
        is_primary: form.responsibles.length === 0, // birinchi — asosiy
    });
    select.value = '';
}

function setPrimary(ri: number) {
    form.responsibles.forEach((r, idx) => { r.is_primary = idx === ri; });
}

function removeResponsible(ri: number) {
    const wasPrimary = form.responsibles[ri]?.is_primary;
    form.responsibles.splice(ri, 1);
    if (wasPrimary && form.responsibles.length && !form.responsibles.some(r => r.is_primary)) {
        form.responsibles[0].is_primary = true;
    }
}

function submit() {
    form.transform(d => ({ ...d, responsibles: d.responsibles.filter(r => r.assignee_id) }))
        .post('/topshiriqlar', { preserveScroll: true });
}
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги топшириқ</h1>
            <p class="mt-1 text-sm text-gray-500">Топшириқни ички ходим(лар)га ёки ташкилот(лар)га бириктиринг.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Topshiriq ma'lumotlari -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Топшириқ маълумотлари</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Сарлавҳа <span class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Топшириқ мазмуни <span class="text-red-500">*</span></label>
                        <textarea v-model="form.task_description" rows="4"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                        <p v-if="form.errors.task_description" class="mt-1 text-xs text-red-600">{{ form.errors.task_description }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Амалга ошириш механизми</label>
                        <textarea v-model="form.implementation" rows="2"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Ижро муддати</label>
                            <input v-model="form.deadline" type="date"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Ҳавола (линк) <span class="text-xs font-normal text-gray-400">— ихтиёрий</span></label>
                            <input v-model="form.link" type="url" placeholder="https://..."
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                            <p v-if="form.errors.link" class="mt-1 text-xs text-red-600">{{ form.errors.link }}</p>
                        </div>
                    </div>

                    <!-- Asosiy hujjat(lar) — ixtiyoriy -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Асосий ҳужжат(лар) <span class="text-xs font-normal text-gray-400">— ихтиёрий</span></label>

                        <div v-if="form.files.length" class="mb-2 space-y-1.5">
                            <div v-for="(f, i) in form.files" :key="i"
                                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-red-500 text-[9px] font-bold text-white">PDF</span>
                                <span class="min-w-0 flex-1 truncate text-xs text-slate-700">{{ f.name }}</span>
                                <span class="shrink-0 text-[10px] text-slate-400">{{ fileSize(f.size) }}</span>
                                <button type="button" @click="removeFile(i)" class="shrink-0 text-slate-400 hover:text-red-600" title="Олиб ташлаш">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 px-4 py-3.5 text-sm text-slate-500 transition hover:border-blue-300 hover:bg-slate-50">
                            <input type="file" multiple @change="onFile" class="hidden" />
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <span>{{ form.files.length ? 'Яна ҳужжат қўшиш' : 'Ҳужжат(лар) танлаш учун босинг' }}</span>
                        </label>
                        <p v-if="form.errors.files" class="mt-1 text-xs text-red-600">{{ form.errors.files }}</p>
                    </div>
                </div>
            </div>

            <!-- Mas'ullar (ijrochilar) -->
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Масъул(лар) <span class="text-red-500">*</span></h2>
                    <!-- Tur tanlash -->
                    <div class="inline-flex rounded-md border border-blue-200 bg-white p-0.5">
                        <button type="button" @click="addType = 'user'"
                            :class="addType === 'user' ? 'bg-blue-600 text-white' : 'text-gray-600'"
                            class="rounded px-3 py-1 text-sm font-medium transition">Ҳокимлик ходими</button>
                        <button type="button" @click="addType = 'organization'"
                            :class="addType === 'organization' ? 'bg-blue-600 text-white' : 'text-gray-600'"
                            class="rounded px-3 py-1 text-sm font-medium transition">Ташкилот</button>
                    </div>
                </div>

                <!-- Tanlab qo'shish -->
                <select @change="addFromSelect($event)"
                    class="mb-3 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">+ {{ addType === 'organization' ? 'Ташкилот' : 'Ходим' }} танлаб қўшинг...</option>
                    <option v-for="a in availableList()" :key="a.id" :value="a.id">{{ a.label }}</option>
                </select>

                <!-- Tanlangan ijrochilar (karta) -->
                <div v-for="(r, ri) in form.responsibles" :key="ri"
                    class="mb-1.5 flex items-center gap-2.5 rounded-md border px-3 py-2 transition"
                    :class="r.is_primary ? 'border-amber-300 bg-amber-50 ring-1 ring-amber-200' : 'border-gray-200 bg-white'">
                    <button type="button" @click="setPrimary(ri)"
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
                            <span v-if="r.is_primary" class="rounded bg-amber-200 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800">Асосий ижрочи</span>
                            <span v-if="r.assignee_type === 'organization'" class="rounded bg-violet-100 px-1.5 py-0.5 text-[10px] text-violet-700">ташкилот</span>
                        </p>
                        <input v-model="r.responsible_position" type="text" placeholder="Лавозими (ихтиёрий)"
                            class="mt-0.5 w-full border-0 bg-transparent p-0 text-xs text-gray-500 focus:ring-0" />
                    </div>
                    <button type="button" @click="removeResponsible(ri)" class="shrink-0 text-red-500 hover:text-red-700" title="Ўчириш">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </div>

                <p v-if="form.responsibles.length" class="mt-1 text-[11px] text-gray-400">★ — асосий ижрочини белгилаш учун юлдузчани босинг</p>
                <p v-else class="text-sm text-gray-400">Масъул ҳали қўшилмаган — юқоридан танлаб қўшинг</p>
                <p v-if="form.errors.responsibles" class="mt-1 text-xs text-red-600">{{ form.errors.responsibles }}</p>
            </div>

            <div class="flex items-center justify-end gap-3">
                <Link href="/topshiriqlar" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Бекор қилиш</Link>
                <button type="submit" :disabled="form.processing || !form.responsibles.length"
                    class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                    {{ form.processing ? 'Сақланмоқда...' : 'Топшириқ бериш' }}
                </button>
            </div>
        </form>
    </AppLayout>
</template>
