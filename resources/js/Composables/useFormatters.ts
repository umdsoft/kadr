import { UZBEK_MONTHS } from '@/Constants/labels';

/**
 * Sana va boshqa formatlash uchun umumiy funksiyalar.
 * DRY: har sahifada qayta yozilgan formatlar shu yerdan oladi.
 */
export function useFormatters() {
    /** "28.12.1995" — qisqa sana */
    function formatDateShort(d: string | null | undefined): string {
        if (!d) return '—';
        const date = new Date(d);
        if (isNaN(date.getTime())) return d;
        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        return `${dd}.${mm}.${date.getFullYear()}`;
    }

    /** "2026 йил декабр" — uzbek tilida oy bilan */
    function formatDateUz(d: string | null | undefined): string {
        if (!d) return '';
        const date = new Date(d);
        if (isNaN(date.getTime())) return String(d);
        return `${date.getFullYear()} йил ${UZBEK_MONTHS[date.getMonth()]}`;
    }

    /** "28.12.1995 14:30" — sana + vaqt */
    function formatDateTime(d: string | null | undefined): string {
        if (!d) return '—';
        const date = new Date(d);
        if (isNaN(date.getTime())) return String(d);
        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const hh = String(date.getHours()).padStart(2, '0');
        const min = String(date.getMinutes()).padStart(2, '0');
        return `${dd}.${mm}.${date.getFullYear()} ${hh}:${min}`;
    }

    /** Fayl hajmi: 1.5 MB / 234 KB / 567 B */
    function formatSize(bytes: number): string {
        if (!bytes || bytes < 1024) return (bytes ?? 0) + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    return { formatDateShort, formatDateUz, formatDateTime, formatSize };
}
