<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Чап панел — Брендинг -->
        <div class="hidden w-1/2 bg-linear-to-br from-blue-700 via-blue-800 to-slate-900 lg:flex lg:flex-col lg:justify-between p-12">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-white">КБТ</p>
                        <p class="text-xs text-blue-200">Кадрлар Бошқарув Тизими</p>
                    </div>
                </div>
            </div>

            <div>
                <h1 class="text-4xl font-bold leading-tight text-white">
                    Хоразм вилояти<br/>ҳокимлиги
                </h1>
                <p class="mt-4 max-w-md text-base leading-relaxed text-blue-200">
                    Электрон кадрлар бошқаруви тизими. Ходимларнинг расмий маълумотномаларини
                    электрон шаклда юритиш, қидирув ва таҳлил қилиш имконияти.
                </p>

                <div class="mt-10 grid grid-cols-3 gap-6">
                    <div class="rounded-xl bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-2xl font-bold text-white">1000+</p>
                        <p class="mt-1 text-xs text-blue-200">Ходимлар</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-2xl font-bold text-white">13</p>
                        <p class="mt-1 text-xs text-blue-200">Туман ва шаҳар</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-2xl font-bold text-white">24/7</p>
                        <p class="mt-1 text-xs text-blue-200">Ишончли тизим</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-blue-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                AES-256 шифрлаш билан ҳимояланган
            </div>
        </div>

        <!-- Ўнг панел — Форма -->
        <div class="flex w-full items-center justify-center bg-slate-50 px-6 lg:w-1/2">
            <div class="w-full max-w-sm">
                <!-- Мобил лого -->
                <div class="mb-8 text-center lg:hidden">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <h2 class="mt-3 text-lg font-bold text-slate-900">КБТ</h2>
                    <p class="text-xs text-slate-400">Хоразм вилояти ҳокимлиги</p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Тизимга кириш</h2>
                    <p class="mt-2 text-sm text-slate-500">Электрон почта ва парол орқали киринг</p>
                </div>

                <!-- Хатолик -->
                <div v-if="form.errors.email && form.errors.email.includes('credentials')"
                    class="mt-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <p class="text-sm text-red-700">Электрон почта ёки парол нотўғри</p>
                </div>

                <form @submit.prevent="submit" class="mt-8 space-y-5">
                    <!-- Электрон почта -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">
                            Электрон почта
                        </label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="admin@kbt.uz"
                                class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-shadow"
                                :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/20': form.errors.email }"
                            />
                        </div>
                        <p v-if="form.errors.email && !form.errors.email.includes('credentials')" class="mt-1.5 text-xs text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Парол -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">
                            Парол
                        </label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="Паролингизни киритинг"
                                class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-shadow"
                                :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/20': form.errors.password }"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                                @click="showPassword = !showPassword"
                            >
                                <svg v-if="!showPassword" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg v-else class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Эслаб қолиш -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20 focus:ring-offset-0 transition-colors"
                            />
                            <span class="text-sm text-slate-600">Эслаб қолиш</span>
                        </label>
                    </div>

                    <!-- Кириш тугмаси -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="relative flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ form.processing ? 'Текширилмоқда...' : 'Тизимга кириш' }}
                    </button>
                </form>

                <p class="mt-8 text-center text-xs text-slate-400">
                    OOO "UMDSOFT" томонидан ишлаб чиқилган
                </p>
            </div>
        </div>
    </div>
</template>
