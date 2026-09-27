<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth?.user);
// Flash error — илгари ҳеч қаерда кўрсатилмасди (20 саҳифа фақат success оларди).
// Энди глобал кўрсатамиз, шунда хатолик хабарлари йўқолиб кетмайди.
const flashError = computed(() => page.props.flash?.error ?? null);
const roles = computed(() => page.props.auth?.roles ?? []);
const tenant = computed(() => page.props.tenant ?? null);

const sidebarOpen = ref(true);
const profileOpen = ref(false);
const tenantSwitcherOpen = ref(false);

// Reaktiv joriy yo'l — Inertia page.url'dan (window.location EMAS, u reaktiv emas).
// Inertia <Link> bilan sahifa qayta yuklanmagani uchun bu shart (M9).
const currentPath = computed(() => page.url.split('?')[0]);

const permissions = computed(() => page.props.auth?.permissions ?? []);
const pendingApprovals = computed(() => (page.props.notifications as { pending_approvals?: number } | undefined)?.pending_approvals ?? 0);
const pendingList = computed(() => (page.props.notifications as { pending_list?: { id: number; title: string; assignee?: string; submitted_at?: string }[] } | undefined)?.pending_list ?? []);
const notificationsOpen = ref(false);
const navCounts = computed(() => (page.props.navCounts as { plans?: number; tasks?: number } | undefined) ?? {});

const tenantTitle = computed(() => {
    if (tenant.value?.is_global) return 'Барча ҳокимликлар';
    return tenant.value?.current?.name_cyr ?? 'Хоразм вилояти ҳокимлиги';
});

function switchTenant(tenantId: number | null) {
    router.post('/tenant/switch', {
        tenant_id: tenantId,
        redirect_to: currentPath.value,
    });
    tenantSwitcherOpen.value = false;
}

const can = (perm: string) => permissions.value.includes(perm) || roles.value.includes('super-admin');

// Навигация — лойиҳа структураси бўйича 3 асосий бўлимга гуруҳланган:
//   1. Кадрлар бўлими   2. Назорат хужжатлар қисми   3. Админ панел
// (type: 'header' — бўлим сарлавҳаси, 'link' — менюдаги ҳавола)
const navigation = computed(() => {
    const items: Array<{ type: 'header' | 'link'; name: string; href?: string; icon?: string; active?: boolean }> = [
        { type: 'link', name: 'Бош саҳифа', href: '/', icon: 'home', active: currentPath.value === '/' },
    ];

    // ===== 1. Кадрлар бўлими =====
    const kadrlar = [];
    if (can('kadrlar.view')) {
        kadrlar.push({ type: 'link' as const, name: 'Ходимлар', href: '/employees', icon: 'users', active: currentPath.value.startsWith('/employees') });
    }
    if (kadrlar.length) {
        items.push({ type: 'header', name: 'Кадрлар бўлими' }, ...kadrlar);
    }

    // ===== 2. Назорат хужжатлар қисми =====
    const nazorat = [];
    if (can('tadbirlar.view')) {
        nazorat.push({ type: 'link' as const, name: 'Назорат режалар', href: '/control-plans', icon: 'calendar', active: currentPath.value.startsWith('/control-plans') });
    }
    if (can('topshiriqlar.assign-org') || can('topshiriqlar.view-own')) {
        nazorat.push({ type: 'link' as const, name: 'Топшириқлар', href: '/topshiriqlar', icon: 'clipboard', active: currentPath.value.startsWith('/topshiriqlar') });
    }
    if (can('tashkilotlar.view')) {
        nazorat.push({ type: 'link' as const, name: 'Ташкилотлар', href: '/organizations', icon: 'building', active: currentPath.value.startsWith('/organizations') });
    }
    if (can('appeals.view')) {
        nazorat.push({ type: 'link' as const, name: 'Мурожаатлар', href: '/appeals', icon: 'inbox', active: currentPath.value.startsWith('/appeals') });
    }
    if (can('meetings.view')) {
        nazorat.push({ type: 'link' as const, name: 'Ёшлар учрашуви', href: '/meetings', icon: 'megaphone', active: currentPath.value.startsWith('/meetings') });
    }
    if (can('councils.view')) {
        nazorat.push({ type: 'link' as const, name: 'Маҳалла еттилиги', href: '/councils', icon: 'home-modern', active: currentPath.value.startsWith('/councils') });
    }
    if (can('hokim-yordamchilari.view')) {
        nazorat.push({ type: 'link' as const, name: 'Ҳоким ёрдамчилари', href: '/hokim-yordamchilari', icon: 'user-group', active: currentPath.value.startsWith('/hokim-yordamchilari') });
    }
    if (can('yoshlar.view')) {
        nazorat.push({ type: 'link' as const, name: 'Ёшлар етакчилари', href: '/yoshlar-yetakchilari', icon: 'sparkles', active: currentPath.value.startsWith('/yoshlar-yetakchilari') });
    }
    if (nazorat.length) {
        items.push({ type: 'header', name: 'Назорат хужжатлар қисми' }, ...nazorat);
    }

    // ===== 3. Админ панел =====
    const admin = [];
    if (can('user.view')) {
        admin.push({ type: 'link' as const, name: 'Фойдаланувчилар', href: '/users', icon: 'user-cog', active: currentPath.value.startsWith('/users') });
    }
    if (can('audit.view')) {
        admin.push({ type: 'link' as const, name: 'Аудит журнали', href: '/audit', icon: 'shield', active: currentPath.value.startsWith('/audit') });
    }
    if (admin.length) {
        items.push({ type: 'header', name: 'Админ панел' }, ...admin);
    }

    return items;
});

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
                    <p class="text-sm font-bold text-slate-900">ХВҲБТ</p>
                    <p class="text-[10px] leading-tight text-slate-400 truncate max-w-[140px]" :title="tenantTitle">{{ tenantTitle }}</p>
                </div>
            </div>

            <!-- Навигация -->
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <template v-for="item in navigation" :key="item.name">
                <p v-if="item.type === 'header'" class="px-3 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                    {{ item.name }}
                </p>
                <Link
                    v-else
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
                    <svg v-else-if="item.icon === 'calendar'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <svg v-else-if="item.icon === 'inbox'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'megaphone'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                    </svg>
                    <svg v-else-if="item.icon === 'home-modern'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                    </svg>
                    <svg v-else-if="item.icon === 'user-group'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'sparkles'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'user-cog'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'shield'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'clipboard'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'building'" class="h-5 w-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    {{ item.name }}
                    <!-- Сон badge'лар -->
                    <span v-if="item.href === '/control-plans' && (navCounts.plans ?? 0) > 0"
                        class="ml-auto rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">
                        {{ navCounts.plans }}
                    </span>
                    <span v-else-if="item.href === '/topshiriqlar'" class="ml-auto flex items-center gap-1">
                        <span v-if="(navCounts.tasks ?? 0) > 0" class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">
                            {{ navCounts.tasks }}
                        </span>
                        <span v-if="pendingApprovals > 0" class="rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white">
                            {{ pendingApprovals }}
                        </span>
                    </span>
                </Link>
                </template>
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

                <!-- Бildirishnoma qo'ng'irog'i — тасдиқлаш кутилмоқда -->
                <div v-if="pendingApprovals > 0" class="relative">
                    <button @click="notificationsOpen = !notificationsOpen"
                        class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Тасдиқлаш кутилмоқда">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ pendingApprovals }}</span>
                    </button>

                    <div v-if="notificationsOpen" class="absolute right-0 top-full z-20 mt-1 w-80 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                        <div class="border-b border-slate-100 px-4 py-2.5">
                            <p class="text-sm font-semibold text-slate-900">⏳ Тасдиқлаш кутилмоқда</p>
                            <p class="text-xs text-slate-400">{{ pendingApprovals }} та ижро тасдиқ кутмоқда</p>
                        </div>
                        <Link v-for="n in pendingList" :key="n.id" :href="`/topshiriqlar/${n.id}`"
                            class="block px-4 py-2.5 hover:bg-amber-50">
                            <p class="truncate text-sm font-medium text-slate-800">{{ n.title }}</p>
                            <p class="truncate text-xs text-slate-400">{{ n.assignee ?? '—' }} · {{ n.submitted_at }}</p>
                        </Link>
                        <Link href="/topshiriqlar" class="block border-t border-slate-100 px-4 py-2 text-center text-xs font-medium text-blue-600 hover:bg-slate-50">
                            Барча топшириқлар →
                        </Link>
                    </div>
                </div>

                <!-- Tenant switcher (faqat super-admin / viloyat-admin uchun) -->
                <div v-if="tenant?.is_cross_tenant" class="relative">
                    <button @click="tenantSwitcherOpen = !tenantSwitcherOpen"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                        {{ tenantTitle }}
                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div v-if="tenantSwitcherOpen"
                        class="absolute right-0 top-full mt-1 w-72 rounded-lg border border-slate-200 bg-white py-1 shadow-lg z-20 max-h-96 overflow-auto">
                        <button @click="switchTenant(null)"
                            class="flex w-full items-center justify-between px-4 py-2 text-sm hover:bg-slate-50"
                            :class="tenant?.is_global ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-700'">
                            <span>Барча ҳокимликлар</span>
                            <svg v-if="tenant?.is_global" class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <hr class="my-1 border-slate-100" />
                        <button v-for="t in (tenant?.available ?? [])" :key="t.id" @click="switchTenant(t.id)"
                            class="flex w-full items-center justify-between px-4 py-2 text-sm hover:bg-slate-50"
                            :class="tenant?.current?.id === t.id ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-700'">
                            <span>{{ t.name_cyr }}</span>
                            <svg v-if="tenant?.current?.id === t.id" class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-else class="flex items-center gap-2 text-xs text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    {{ tenantTitle }}
                </div>
            </header>

            <!-- Саҳифа контенти -->
            <main class="p-6">
                <!-- Глобал флеш error — илгари ҳеч қаерда кўрсатилмасди (success ҳар саҳифада ўзида бор) -->
                <div v-if="flashError" class="mb-4 flex items-start gap-2 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ flashError }}</span>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
