<script setup lang="ts">
// Асосий layout — sidebar + header skeleti
// Phase 7 да тўлдирилади
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white border-b border-gray-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center">
                        <span class="text-xl font-bold text-blue-700">КБТ</span>
                        <span class="ml-2 text-sm text-gray-500">Кадрлар Бошқарув Тизими</span>
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>
