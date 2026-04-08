import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

/**
 * Flash xabarlarni ko'rsatish uchun composable.
 */
export function useFlash() {
    const page = usePage<PageProps>();

    const success = computed(() => page.props.flash?.success ?? null);
    const error = computed(() => page.props.flash?.error ?? null);

    return { success, error };
}
