import { ref, watch } from 'vue';
import type { Region, District, Mahalla, Nationality, Department, Position, Specialty } from '@/types';

/**
 * Гео каталог ва бошқа справочниклар учун composable.
 * DRY: бир жойдан барча dropdown маълумотлари.
 */
export function useGeoCatalog() {
    const regions = ref<Region[]>([]);
    const districts = ref<District[]>([]);
    const mahallas = ref<Mahalla[]>([]);
    const nationalities = ref<Nationality[]>([]);
    const departments = ref<Department[]>([]);
    const positions = ref<Position[]>([]);
    const specialties = ref<Specialty[]>([]);
    const loading = ref(false);

    async function fetchRegions() {
        const res = await fetch('/api/catalogs/regions');
        regions.value = await res.json();
    }

    async function fetchDistricts(regionId: number) {
        districts.value = [];
        mahallas.value = [];
        if (!regionId) return;
        const res = await fetch(`/api/catalogs/regions/${regionId}/districts`);
        districts.value = await res.json();
    }

    async function fetchMahallas(districtId: number) {
        mahallas.value = [];
        if (!districtId) return;
        const res = await fetch(`/api/catalogs/districts/${districtId}/mahallas`);
        mahallas.value = await res.json();
    }

    async function fetchNationalities() {
        const res = await fetch('/api/catalogs/nationalities');
        nationalities.value = await res.json();
    }

    async function fetchDepartments() {
        const res = await fetch('/api/catalogs/departments');
        departments.value = await res.json();
    }

    async function fetchPositions(departmentId?: number) {
        const url = departmentId
            ? `/api/catalogs/departments/${departmentId}/positions`
            : '/api/catalogs/positions';
        const res = await fetch(url);
        positions.value = await res.json();
    }

    async function fetchSpecialties() {
        const res = await fetch('/api/catalogs/specialties');
        specialties.value = await res.json();
    }

    async function fetchAll() {
        loading.value = true;
        await Promise.all([
            fetchRegions(),
            fetchNationalities(),
            fetchDepartments(),
            fetchPositions(),
            fetchSpecialties(),
        ]);
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
