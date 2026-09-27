<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormField from '@/Components/UI/FormField.vue';
import { MEETING_STATUS_LABELS } from '@/Constants/labels';
import type { YouthMeeting } from '@/types';

interface Props {
    meeting?: YouthMeeting | null;
    users: { id: number; name: string }[];
    mahallas: { id: number; name_cyr: string }[];
    submitUrl: string;
    method: 'post' | 'put';
    submitLabel: string;
}

const props = defineProps<Props>();

const form = useForm({
    mahalla_id: props.meeting?.mahalla_id ?? null,
    chairman_id: props.meeting?.chairman_id ?? null,
    meeting_date: props.meeting?.meeting_date ? String(props.meeting.meeting_date).split('T')[0] : '',
    meeting_time: props.meeting?.meeting_time ?? '',
    location: props.meeting?.location ?? '',
    participants_count: props.meeting?.participants_count ?? 0,
    agenda: props.meeting?.agenda ?? '',
    notes: props.meeting?.notes ?? '',
    status: props.meeting?.status ?? 'planned',
});

function submit() {
    if (props.method === 'put') form.put(props.submitUrl);
    else form.post(props.submitUrl);
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div class="rounded-lg bg-white p-6 shadow space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <FormField label="Сана" required :error="form.errors.meeting_date">
                    <input v-model="form.meeting_date" type="date"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
                <FormField label="Вақт">
                    <input v-model="form.meeting_time" type="time"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
                <FormField label="Ҳолат">
                    <select v-model="form.status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option v-for="(label, key) in MEETING_STATUS_LABELS" :key="key" :value="key">{{ label }}</option>
                    </select>
                </FormField>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <FormField label="Жой">
                    <input v-model="form.location" type="text"
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
                <FormField label="Раис" required :error="form.errors.chairman_id">
                    <select v-model="form.chairman_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option :value="null">— Танланг —</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </FormField>
                <FormField label="Қатнашчилар сони">
                    <input v-model="form.participants_count" type="number" min="0"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
            </div>

            <FormField label="Кун тартиби (агенда)">
                <textarea v-model="form.agenda" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
            </FormField>

            <FormField label="Изоҳлар">
                <textarea v-model="form.notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
            </FormField>
        </div>

        <div class="flex justify-end gap-3">
            <Link href="/meetings" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Бекор</Link>
            <button type="submit" :disabled="form.processing"
                class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>
