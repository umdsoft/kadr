<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

interface Kompleks { id: number; name_cyr: string }

const props = withDefaults(defineProps<{
    organization?: Record<string, any> | null;
    komplekslar: Kompleks[];
    submitUrl: string;
    method?: 'post' | 'put';
    submitLabel?: string;
}>(), { method: 'post', submitLabel: 'Сақлаш', organization: null });

const form = useForm({
    name_cyr: props.organization?.name_cyr ?? '',
    name_lat: props.organization?.name_lat ?? '',
    inn: props.organization?.inn ?? '',
    phone: props.organization?.phone ?? '',
    address: props.organization?.address ?? '',
    kompleks_id: props.organization?.kompleks_id ?? null,
    is_active: props.organization?.is_active ?? true,
});

function submit() {
    const opts = { preserveScroll: true };
    props.method === 'put' ? form.put(props.submitUrl, opts) : form.post(props.submitUrl, opts);
}
</script>

<template>
    <form @submit.prevent="submit" class="max-w-2xl space-y-5 rounded-lg bg-white p-6 shadow">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Ташкилот номи (кирилл) <span class="text-red-500">*</span></label>
            <input v-model="form.name_cyr" type="text"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            <p v-if="form.errors.name_cyr" class="mt-1 text-xs text-red-600">{{ form.errors.name_cyr }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Ташкилот номи (лотин)</label>
            <input v-model="form.name_lat" type="text"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">СТИР (ИНН)</label>
                <input v-model="form.inn" type="text" maxlength="9" placeholder="9 рақам"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                <p v-if="form.errors.inn" class="mt-1 text-xs text-red-600">{{ form.errors.inn }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Телефон</label>
                <input v-model="form.phone" type="text" placeholder="+998 ..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Манзил</label>
            <input v-model="form.address" type="text"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
        </div>

        <div v-if="komplekslar.length">
            <label class="mb-1 block text-sm font-medium text-gray-700">Комплекс</label>
            <select v-model="form.kompleks_id"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                <option :value="null">— Танланг (ихтиёрий) —</option>
                <option v-for="k in komplekslar" :key="k.id" :value="k.id">{{ k.name_cyr }}</option>
            </select>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300" />
            Фаол
        </label>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" :disabled="form.processing"
                class="rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                {{ form.processing ? 'Сақланмоқда...' : submitLabel }}
            </button>
            <Link href="/organizations" class="text-sm text-gray-500 hover:text-gray-700">Бекор қилиш</Link>
        </div>
    </form>
</template>
