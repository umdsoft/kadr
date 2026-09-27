<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import FormField from '@/Components/UI/FormField.vue';
import { APPEAL_PRIORITY_LABELS, APPEAL_SOURCE_LABELS } from '@/Constants/labels';
import type { CitizenAppeal, AppealCategory } from '@/types';

interface Props {
    appeal?: CitizenAppeal | null;
    categories: AppealCategory[];
    mahallas: { id: number; name_cyr: string }[];
    meetings: { id: number; meeting_date: string; location: string | null }[];
    submitUrl: string;
    method: 'post' | 'put';
    submitLabel: string;
}

const props = defineProps<Props>();

const form = useForm({
    mahalla_id: props.appeal?.mahalla_id ?? null,
    youth_meeting_id: props.appeal?.youth_meeting_id ?? null,
    applicant_name: props.appeal?.applicant_name ?? '',
    applicant_phone: props.appeal?.applicant_phone ?? '',
    applicant_birth_date: props.appeal?.applicant_birth_date ? String(props.appeal.applicant_birth_date).split('T')[0] : '',
    applicant_address: props.appeal?.applicant_address ?? '',
    body: props.appeal?.body ?? '',
    amount: props.appeal?.amount ?? null,
    category_id: props.appeal?.category_id ?? null,
    sub_category_id: props.appeal?.sub_category_id ?? null,
    priority: props.appeal?.priority ?? 'normal',
    source: props.appeal?.source ?? 'web',
});

const subCategories = computed<AppealCategory[]>(() => {
    if (!form.category_id) return [];
    const root = props.categories.find(c => c.id === form.category_id);
    return root?.children ?? [];
});

function onCategoryChange() {
    form.sub_category_id = null;
}

function submit() {
    if (props.method === 'put') form.put(props.submitUrl);
    else form.post(props.submitUrl);
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <!-- Murojaatchi ma'lumotlari -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Мурожаатчи маълумотлари</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <FormField label="Ф.И.Ш." required :error="form.errors.applicant_name">
                    <input v-model="form.applicant_name" type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>

                <FormField label="Телефон" :error="form.errors.applicant_phone">
                    <input v-model="form.applicant_phone" type="text" placeholder="+998..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <FormField label="Туғилган санаси">
                    <input v-model="form.applicant_birth_date" type="date"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>

                <FormField label="Манзил">
                    <input v-model="form.applicant_address" type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
            </div>

            <div class="mt-4">
                <FormField label="Маҳалла">
                    <select v-model="form.mahalla_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">— Танланмаган —</option>
                        <option v-for="m in mahallas" :key="m.id" :value="m.id">{{ m.name_cyr }}</option>
                    </select>
                </FormField>
            </div>
        </div>

        <!-- Murojaat mazmuni -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Мурожаат мазмуни</h2>

            <FormField label="Мурожаат матни" required :error="form.errors.body">
                <textarea v-model="form.body" rows="5"
                    placeholder="Мурожаат батафсил баён этилсин..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
            </FormField>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <FormField label="Категория">
                    <select v-model="form.category_id" @change="onCategoryChange"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">— АИ автоматик аниқлайди —</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_cyr }}</option>
                    </select>
                </FormField>

                <FormField label="Кичик категория">
                    <select v-model="form.sub_category_id" :disabled="!form.category_id"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100">
                        <option :value="null">{{ form.category_id ? '— Танланг —' : '— Аввал категория танланг —' }}</option>
                        <option v-for="sc in subCategories" :key="sc.id" :value="sc.id">{{ sc.name_cyr }}</option>
                    </select>
                </FormField>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <FormField label="Муҳимлиги">
                    <select v-model="form.priority" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option v-for="(label, key) in APPEAL_PRIORITY_LABELS" :key="key" :value="key">{{ label }}</option>
                    </select>
                </FormField>

                <FormField label="Манба">
                    <select v-model="form.source" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option v-for="(label, key) in APPEAL_SOURCE_LABELS" :key="key" :value="key">{{ label }}</option>
                    </select>
                </FormField>

                <FormField label="Сумма / Гектар (ихтиёрий)">
                    <input v-model="form.amount" type="number" step="0.01" min="0"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
            </div>

            <div class="mt-4">
                <FormField label="Учрашув (агар маълум бўлса)">
                    <select v-model="form.youth_meeting_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">— Танланмаган —</option>
                        <option v-for="m in meetings" :key="m.id" :value="m.id">
                            {{ m.meeting_date }} — {{ m.location ?? '' }}
                        </option>
                    </select>
                </FormField>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <Link href="/appeals" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Бекор қилиш</Link>
            <button type="submit" :disabled="form.processing"
                class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>
