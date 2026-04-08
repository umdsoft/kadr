<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => page.props.auth?.roles ?? []);

const sidebarOpen = ref(true);
const profileOpen = ref(false);

const currentPath = computed(() => window.location.pathname);

const navigation = computed(() => [
    {
        name: 'Бош саҳифа',
        href: '/',
        icon: 'home',
        active: currentPath.value === '/',
    },
    {
        name: 'Ходимлар',
        href: '/employees',
        icon: 'users',
        active: currentPath.value.startsWith('/employees'),
    },
    {
        name: 'Аудит журнали',
        href: '/audit',
        icon: 'shield',
        active: currentPath.value.startsWith('/audit'),
    },
]);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform duration-200"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">КБТ</p>
                    <p class="text-[10px] leading-tight text-slate-400">Кадрлар Бошқарув Тизими</p>
                </div>
            </div>

            <!-- Навигация -->
            <nav class="flex-1 space-y-1 px-3 py-4">
                <a
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                    :class="item.active
                        ? 'bg-blue-50 text-blue-700'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                >
                    <!-- Иконлар -->
                    <svg v-if="item.icon === 'home'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <svg v-else-if="item.icon === 'users'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'shield'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    {{ item.name }}
                </a>
            </nav>

            <!-- Фойдаланувчи панели -->
            <div class="border-t border-slate-200 p-3">
                <div class="relative">
                    <button
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left hover:bg-slate-100 transition-colors"
                        @click="profileOpen = !profileOpen"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700">
                            {{ user?.name?.charAt(0) ?? 'U' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ user?.name ?? 'Фойдаланувчи' }}</p>
                            <p class="truncate text-xs text-slate-400">{{ roles[0] ?? '' }}</p>
                        </div>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="{ 'rotate-180': profileOpen }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div v-if="profileOpen" class="absolute bottom-full left-0 right-0 mb-1 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                        <button
                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                            @click="logout"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            Чиқиш
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Мобил sidebar overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-20 bg-black/20 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Асосий контент -->
        <div class="lg:pl-64">
            <!-- Юқори панел -->
            <header class="sticky top-0 z-10 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/80 px-6 backdrop-blur-sm">
                <button
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 lg:hidden"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <div class="flex-1" />

                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    Хоразм вилояти ҳокимлиги
                </div>
            </header>

            <!-- Саҳифа контенти -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
