<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormField from '@/Components/UI/FormField.vue';
import type { YoshlarYetakchisi } from '@/types';

interface Props {
    item?: YoshlarYetakchisi | null;
    users: { id: number; name: string }[];
    mahallas: { id: number; name_cyr: string }[];
    submitUrl: string;
    method: 'post' | 'put';
    submitLabel: string;
}

const props = defineProps<Props>();

const form = useForm({
    user_id: props.item?.user_id ?? null,
    mahalla_id: props.item?.mahalla_id ?? null,
    full_name_cyr: props.item?.full_name_cyr ?? '',
    phone: props.item?.phone ?? '',
    birth_date: props.item?.birth_date ? String(props.item.birth_date).split('T')[0] : '',
    start_date: props.item?.start_date ? String(props.item.start_date).split('T')[0] : '',
    end_date: props.item?.end_date ? String(props.item.end_date).split('T')[0] : '',
    is_active: props.item?.is_active ?? true,
    notes: props.item?.notes ?? '',
});

function submit() {
    if (props.method === 'put') form.put(props.submitUrl);
    else form.post(props.submitUrl);
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div class="rounded-lg bg-white p-6 shadow space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <FormField label="Тизим фойдаланувчиси (ихтиёрий)">
                    <select v-model="form.user_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">— Танланмаган —</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </FormField>

                <FormField label="Ф.И.Ш. (Кирилл)" required :error="form.errors.full_name_cyr">
                    <input v-model="form.full_name_cyr" type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <FormField label="Телефон">
                    <input v-model="form.phone" type="text" placeholder="+998..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>

                <FormField label="Туғилган санаси">
                    <input v-model="form.birth_date" type="date"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>

                <FormField label="Маҳалла">
                    <select v-model="form.mahalla_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">— Танланмаган —</option>
                        <option v-for="m in mahallas" :key="m.id" :value="m.id">{{ m.name_cyr }}</option>
                    </select>
                </FormField>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <FormField label="Бошланиш санаси" required :error="form.errors.start_date">
                    <input v-model="form.start_date" type="date"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>

                <FormField label="Тугаш санаси">
                    <input v-model="form.end_date" type="date"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
            </div>

            <FormField label="Изоҳлар">
                <textarea v-model="form.notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
            </FormField>

            <label class="flex items-center gap-2">
                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                <span class="text-sm text-gray-700">Фаол</span>
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <Link href="/yoshlar-yetakchilari" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Бекор қилиш</Link>
            <button type="submit" :disabled="form.processing"
                class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>
