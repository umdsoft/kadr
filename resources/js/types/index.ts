// === Каталог типлари ===

export interface Region {
    id: number;
    name_cyr: string;
    name_lat: string;
    code: string;
}

export interface District {
    id: number;
    region_id: number;
    name_cyr: string;
    name_lat: string;
    code: string;
    is_city: boolean;
}

export interface Mahalla {
    id: number;
    district_id: number;
    name_cyr: string;
    name_lat: string;
}

export interface Department {
    id: number;
    parent_id: number | null;
    name_cyr: string;
    name_lat: string;
    code: string | null;
    children?: Department[];
}

export interface Position {
    id: number;
    department_id: number | null;
    name_cyr: string;
    name_lat: string;
}

export interface Nationality {
    id: number;
    name_cyr: string;
    name_lat: string;
}

export interface Specialty {
    id: number;
    name_cyr: string;
    name_lat: string;
}

// === Ходим типлари ===

export interface Employee {
    id: number;
    uuid: string;
    // 1-блок
    last_name_cyr: string;
    first_name_cyr: string;
    middle_name_cyr: string;
    last_name_lat: string | null;
    first_name_lat: string | null;
    middle_name_lat: string | null;
    current_position: string;
    position_start_date: string;
    photo_path: string | null;
    // 2-блок
    birth_date: string;
    birth_place: string;
    birth_region_id: number;
    birth_district_id: number;
    nationality: string;
    party_affiliation: string;
    education_level: EducationLevel;
    education_completion: string;
    specialty_by_education: string;
    academic_degree: string;
    academic_title: string;
    foreign_languages: string;
    state_awards: string;
    elected_body_member: string;
    // Махфий
    jshshir: string;
    passport_series: string;
    passport_number: string;
    // Хизмат
    department_id: number;
    position_id: number;
    // Муносабатлар
    department?: Department;
    position?: Position;
    birth_region?: Region;
    birth_district?: District;
    work_history?: WorkHistory[];
    relatives?: Relative[];
    // Тизим
    full_name: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export type EducationLevel = 'олий' | 'тугалланмаган олий' | 'ўрта махсус' | 'ўрта';

export const EDUCATION_LEVELS: { value: EducationLevel; label: string }[] = [
    { value: 'олий', label: 'Олий' },
    { value: 'тугалланмаган олий', label: 'Тугалланмаган олий' },
    { value: 'ўрта махсус', label: 'Ўрта махсус' },
    { value: 'ўрта', label: 'Ўрта' },
];

export const RELATIONSHIP_TYPES = [
    'Отаси', 'Онаси', 'Опаси', 'Синглиси', 'Акаси', 'Укаси',
    'Турмуш ўртоғи', 'Ўғли', 'Қизи', 'Қайнотаси', 'Қайнонаси',
    'Қайнукаси', 'Қайнсинглиси', 'Невараси', 'Келини', 'Куёви',
] as const;

export type RelationshipType = typeof RELATIONSHIP_TYPES[number];

// === 3-блок ===

export interface WorkHistory {
    id?: number;
    employee_id?: number;
    start_year: number;
    end_year: number | null;
    organization_full: string;
    position_full: string;
    order_number: string | null;
    order_date: string | null;
    sort_order: number;
}

// === 4-блок ===

export interface Relative {
    id?: number;
    employee_id?: number;
    relationship_type: RelationshipType | string;
    full_name_cyr: string;
    birth_year: number;
    birth_place: string;
    is_deceased: boolean;
    deceased_year: number | null;
    workplace_and_position: string;
    former_position?: string;
    residence_full: string;
}

// === Pagination ===

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

// === Auth ===

export interface User {
    id: number;
    name: string;
    email: string;
    department_id: number | null;
}

export interface PageProps {
    auth: {
        user: User | null;
        roles: string[];
        permissions: string[];
    };
    flash: {
        success: string | null;
        error: string | null;
    };
}

// === Qidiruv filtrlari ===

export interface SearchFilters {
    search?: string;
    department_id?: number | null;
    education_level?: EducationLevel | null;
    nationality?: string | null;
    birth_district_id?: number | null;
    specialty?: string | null;
    birth_date_range?: {
        from?: string;
        to?: string;
    };
}
