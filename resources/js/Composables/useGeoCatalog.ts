import { ref } from 'vue';
import type { Region, District, Mahalla, Nationality, Department, Position, Specialty } from '@/types';

/**
 * Гео каталог ва бошқа справочниклар учун composable.
 * DRY: бир жойдан барча dropdown маълумотлари.
 *
 * - Хатоликка чидамли: fetch муваффақиятсиз бўлса (500 / auth-redirect) dropdown
 *   жимгина бўш қолмайди — консолга ёзилади ва бўш массив қайтарилади (M7).
 * - Кеш: статик каталоглар (вилоят, миллат, бўлим, лавозим, мутахассислик)
 *   модул даражасида бир марта юкланади ва барча компонентлар ўртасида улашилади.
 */

// Модул-даражали кеш (статик каталоглар) — барча компонентлар учун битта манба.
const regions = ref<Region[]>([]);
const nationalities = ref<Nationality[]>([]);
const departments = ref<Department[]>([]);
const positions = ref<Position[]>([]);
const specialties = ref<Specialty[]>([]);
let staticLoaded = false;

async function safeJson<T>(url: string): Promise<T[]> {
    try {
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!res.ok) {
            console.error(`Каталог юкланмади (HTTP ${res.status}): ${url}`);
            return [];
        }
        return (await res.json()) as T[];
    } catch (e) {
        console.error(`Каталог хатоси: ${url}`, e);
        return [];
    }
}

export function useGeoCatalog() {
    // Динамик (танланган вилоят/туманга боғлиқ) — ҳар компонентда алоҳида.
    const districts = ref<District[]>([]);
    const mahallas = ref<Mahalla[]>([]);
    const loading = ref(false);

    async function fetchRegions() {
        regions.value = await safeJson<Region>('/api/catalogs/regions');
    }

    // ID'lar UUID (satr) — number|string ikkalasini ham qabul qilamiz.
    async function fetchDistricts(regionId: number | string | null) {
        districts.value = [];
        mahallas.value = [];
        if (!regionId) return;
        districts.value = await safeJson<District>(`/api/catalogs/regions/${regionId}/districts`);
    }

    async function fetchMahallas(districtId: number | string | null) {
        mahallas.value = [];
        if (!districtId) return;
        mahallas.value = await safeJson<Mahalla>(`/api/catalogs/districts/${districtId}/mahallas`);
    }

    async function fetchNationalities() {
        nationalities.value = await safeJson<Nationality>('/api/catalogs/nationalities');
    }

    async function fetchDepartments() {
        departments.value = await safeJson<Department>('/api/catalogs/departments');
    }

    async function fetchPositions(departmentId?: number) {
        const url = departmentId
            ? `/api/catalogs/departments/${departmentId}/positions`
            : '/api/catalogs/positions';
        positions.value = await safeJson<Position>(url);
    }

    async function fetchSpecialties() {
        specialties.value = await safeJson<Specialty>('/api/catalogs/specialties');
    }

    /** Барча статик каталогларни юклаш. Кешланган бўлса қайта юкламайди (force=true мажбурлайди). */
    async function fetchAll(force = false) {
        if (staticLoaded && !force) return;
        loading.value = true;
        await Promise.all([
            fetchRegions(),
            fetchNationalities(),
            fetchDepartments(),
            fetchPositions(),
            fetchSpecialties(),
        ]);
        staticLoaded = true;
        loading.value = false;
    }

    return {
        regions, districts, mahallas, nationalities,
        departments, positions, specialties, loading,
        fetchRegions, fetchDistricts, fetchMahallas,
        fetchNationalities, fetchDepartments, fetchPositions,
        fetchSpecialties, fetchAll,
    };
}
