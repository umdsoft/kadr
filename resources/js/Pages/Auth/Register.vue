<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100">
        <div class="w-full max-w-md">
            <div class="rounded-lg bg-white px-8 py-10 shadow-md">
                <div class="mb-6 text-center">
                    <h1 class="text-xl font-bold text-gray-900">Янги фойдаланувчи</h1>
                    <p class="mt-1 text-xs text-gray-400">
                        Фақат администратор яратиши мумкин
                    </p>
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label for="name" class="mb-1 block text-sm font-medium text-gray-700">
                            Тўлиқ исм
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="mb-1 block text-sm font-medium text-gray-700">
                            Электрон почта
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="mb-1 block text-sm font-medium text-gray-700">
                            Парол
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">
                            Паролни тасдиқланг
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        Рўйхатдан ўтказиш
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
