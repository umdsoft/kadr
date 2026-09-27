<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import FormField from '@/Components/UI/FormField.vue';
import { COUNCIL_ROLE_LABELS } from '@/Constants/labels';
import type { MahallaCouncil, CouncilMemberRole } from '@/types';

interface Props {
    council?: MahallaCouncil | null;
    mahallas: { id: number; name_cyr: string }[];
    users: { id: number; name: string }[];
    submitUrl: string;
    method: 'post' | 'put';
    submitLabel: string;
}

const props = defineProps<Props>();

interface MemberInput {
    id?: number;
    user_id: number | null;
    full_name: string;
    role: CouncilMemberRole;
    phone: string;
}

const members = ref<MemberInput[]>(
    (props.council?.members ?? []).map(m => ({
        id: m.id,
        user_id: m.user_id,
        full_name: m.full_name,
        role: m.role,
        phone: m.phone ?? '',
    }))
);

const form = useForm({
    mahalla_id: props.council?.mahalla_id ?? null,
    name: props.council?.name ?? 'Маҳалла еттилиги',
    phone: props.council?.phone ?? '',
    is_active: props.council?.is_active ?? true,
});

function addMember() {
    members.value.push({ user_id: null, full_name: '', role: 'rais' as CouncilMemberRole, phone: '' });
}

function removeMember(i: number) {
    members.value.splice(i, 1);
}

function submit() {
    const data = form.transform(d => ({ ...d, members: members.value }));
    if (props.method === 'put') data.put(props.submitUrl);
    else data.post(props.submitUrl);
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div class="rounded-lg bg-white p-6 shadow space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Еттилик маълумотлари</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <FormField label="Маҳалла" required :error="form.errors.mahalla_id">
                    <select v-model="form.mahalla_id" :disabled="method === 'put'"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100">
                        <option :value="null">— Танланг —</option>
                        <option v-for="m in mahallas" :key="m.id" :value="m.id">{{ m.name_cyr }}</option>
                    </select>
                </FormField>
                <FormField label="Еттилик номи">
                    <input v-model="form.name" type="text" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
                <FormField label="Телефон">
                    <input v-model="form.phone" type="text" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </FormField>
            </div>

            <label class="flex items-center gap-2">
                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                <span class="text-sm text-gray-700">Фаол</span>
            </label>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Еттилик аъзолари</h2>
                <button type="button" @click="addMember"
                    class="rounded-md bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">+ Аъзо қўшиш</button>
            </div>

            <div v-for="(m, i) in members" :key="i" class="mb-3 rounded-md border border-gray-200 p-3">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">{{ i + 1 }}-аъзо</span>
                    <button type="button" @click="removeMember(i)" class="text-sm text-red-600">Ўчириш</button>
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div>
                        <label class="text-xs text-gray-600">Лавозим</label>
                        <select v-model="m.role" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                            <option v-for="(label, key) in COUNCIL_ROLE_LABELS" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Тизим фойдаланувчиси</label>
                        <select v-model="m.user_id" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                            <option :value="null">— Танланмаган —</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Ф.И.Ш.</label>
                        <input v-model="m.full_name" type="text" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Телефон</label>
                        <input v-model="m.phone" type="text" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" />
                    </div>
                </div>
            </div>

            <p v-if="members.length === 0" class="text-sm text-gray-400 italic py-4 text-center">Аъзолар қўшилмаган</p>
        </div>

        <div class="flex justify-end gap-3">
            <Link href="/councils" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Бекор</Link>
            <button type="submit" :disabled="form.processing"
                class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>
