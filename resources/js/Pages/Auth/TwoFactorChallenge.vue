<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const useRecoveryCode = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const submit = () => {
    form.post('/two-factor-challenge', {
        onFinish: () => {
            form.reset('code', 'recovery_code');
        },
    });
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100">
        <div class="w-full max-w-md">
            <div class="rounded-lg bg-white px-8 py-10 shadow-md">
                <div class="mb-6 text-center">
                    <h1 class="text-xl font-bold text-gray-900">Икки босқичли тасдиқлаш</h1>
                    <p class="mt-2 text-sm text-gray-500">
                        <template v-if="!useRecoveryCode">
                            Google Authenticator иловасидаги кодни киритинг
                        </template>
                        <template v-else>
                            Тиклаш кодларидан бирини киритинг
                        </template>
                    </p>
                </div>

                <form @submit.prevent="submit">
                    <div v-if="!useRecoveryCode" class="mb-4">
                        <label for="code" class="mb-1 block text-sm font-medium text-gray-700">
                            Код
                        </label>
                        <input
                            id="code"
                            v-model="form.code"
                            type="text"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            maxlength="6"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-center text-lg tracking-widest focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.code }"
                        />
                        <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <div v-else class="mb-4">
                        <label for="recovery_code" class="mb-1 block text-sm font-medium text-gray-700">
                            Тиклаш коди
                        </label>
                        <input
                            id="recovery_code"
                            v-model="form.recovery_code"
                            type="text"
                            autocomplete="one-time-code"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.recovery_code }"
                        />
                        <p v-if="form.errors.recovery_code" class="mt-1 text-xs text-red-600">
                            {{ form.errors.recovery_code }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mb-3 w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Текширилмоқда...</span>
                        <span v-else>Тасдиқлаш</span>
                    </button>

                    <button
                        type="button"
                        class="w-full text-center text-sm text-gray-500 hover:text-gray-700"
                        @click="useRecoveryCode = !useRecoveryCode"
                    >
                        {{ useRecoveryCode ? 'Authenticator коди билан кириш' : 'Тиклаш кодини ишлатиш' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
