<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useGeoCatalog } from '@/Composables/useGeoCatalog';

const { departments, positions, fetchAll, fetchPositions } = useGeoCatalog();
onMounted(async () => {
    await fetchAll();
    await fetchPositions();
});

const selectedHokimlikId = ref<number | null>(null);

// Тanlangan ҳокимликнинг бошқармалари
const boshqarmalar = computed(() => {
    if (!selectedHokimlikId.value) return [];
    const hok = departments.value.find(d => d.id === selectedHokimlikId.value);
    return hok?.children ?? [];
});

const form = useForm({
    name: '',
    login: '',
    password: '',
    department_id: null as number | null, // Бошқарма ID ёки ҳокимлик ID
    position_id: null as number | null,
});

function onHokimlikChange() {
    form.department_id = null;
}

function submit() {
    // Агар бошқарма танланмаган бўлса, ҳокимлик ID ни юборамиз
    if (!form.department_id && selectedHokimlikId.value) {
        form.department_id = selectedHokimlikId.value;
    }
    form.post('/users');
}
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Янги фойдаланувчи</h1>
        </div>

        <div class="mx-auto max-w-2xl rounded-lg bg-white p-6 shadow">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Исми *</label>
                    <input v-model="form.name" type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.name }" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Логин *</label>
                    <input v-model="form.login" type="text" placeholder="masalan: a.davletov"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.login }" />
                    <p v-if="form.errors.login" class="mt-1 text-xs text-red-600">{{ form.errors.login }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Парол *</label>
                    <input v-model="form.password" type="password"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.password }" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>

                <!-- 1. Ҳокимлик -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Ҳокимлик *</label>
                    <select v-model="selectedHokimlikId" @change="onHokimlikChange"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option :value="null">— Танланг —</option>
                        <optgroup label="Вилоят ҳокимлиги">
                            <option v-for="dept in departments.filter(d => d.type === 'viloyat')" :key="dept.id" :value="dept.id"
                                class="font-bold">{{ dept.name_cyr }}</option>
                        </optgroup>
                        <optgroup label="Шаҳар ҳокимликлари">
                            <option v-for="dept in departments.filter(d => d.type === 'shahar')" :key="dept.id" :value="dept.id">{{ dept.name_cyr }}</option>
                        </optgroup>
                        <optgroup label="Туман ҳокимликлари">
                            <option v-for="dept in departments.filter(d => d.type === 'tuman')" :key="dept.id" :value="dept.id">{{ dept.name_cyr }}</option>
                        </optgroup>
                    </select>
                </div>

                <!-- 2. Бошқарма -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Бошқарма / Бўлим *</label>
                    <select v-model="form.department_id"
                        :disabled="!selectedHokimlikId"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400"
                        :class="{ 'border-red-500': form.errors.department_id }">
                        <option :value="null">{{ selectedHokimlikId ? '— Танланг —' : '— Аввал ҳокимлик танланг —' }}</option>
                        <option v-if="selectedHokimlikId" :value="selectedHokimlikId" class="font-medium text-blue-700">Ҳоким маслаҳатчиси (бўлимга бўйсинмайди)</option>
                        <option v-for="b in boshqarmalar" :key="b.id" :value="b.id">{{ b.name_cyr }}</option>
                    </select>
                    <p v-if="form.errors.department_id" class="mt-1 text-xs text-red-600">{{ form.errors.department_id }}</p>
                </div>

                <!-- 3. Лавозим -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Лавозими *</label>
                    <select v-model="form.position_id"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.position_id }">
                        <option :value="null">— Танланг —</option>
                        <option v-for="p in positions" :key="p.id" :value="p.id">{{ p.name_cyr }}</option>
                    </select>
                    <p v-if="form.errors.position_id" class="mt-1 text-xs text-red-600">{{ form.errors.position_id }}</p>
                    <p class="mt-1 text-xs text-gray-400">Тизимдаги ҳуқуқлар лавозимга мос автоматик берилади</p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                    <Link href="/users" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Бекор қилиш</Link>
                    <button type="submit" :disabled="form.processing"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                        Яратиш
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
