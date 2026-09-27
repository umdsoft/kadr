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
    type: string | null;
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
    photo_url: string | null;
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
    login?: string;
    email?: string | null;
    department_id: number | null;
    position_id?: number | null;
    department?: Department | null;
    position?: Position | null;
    roles?: { id: number; name: string }[];
}

// === Назорат режа ===

export type ExecutionStatus = 'not_started' | 'in_progress' | 'completed' | 'overdue';
export type PlanStatus = 'active' | 'completed' | 'archived';

export interface ItemResponsible {
    id?: number;
    user_id: number | null;
    responsible_name: string;
    responsible_position: string | null;
    display_position?: string | null;
    is_primary?: boolean;
    assignee_type?: string;
    user?: User | null;
}

export interface ItemDocumentItem {
    id: number;
    file_path: string;
    original_name: string;
    file_size: number;
    mime_type: string | null;
    description: string | null;
    uploaded_by: number;
    created_at: string;
    uploader?: { id: number; name: string } | null;
}

export interface ControlPlanItem {
    id: number;
    control_plan_id: number;
    item_number: string;
    section_title: string | null;
    task_description: string;
    implementation: string | null;
    funding_source: string | null;
    deadline: string | null;
    execution_status: ExecutionStatus;
    execution_report: string | null;
    sort_order: number;
    can_edit?: boolean;
    responsibles?: ItemResponsible[];
    documents?: ItemDocumentItem[];
    plan?: ControlPlan;
}

export interface ControlPlan {
    id: number;
    uuid: string;
    title: string;
    document_number: string | null;
    document_date: string | null;
    status: PlanStatus;
    status_date: string | null;
    created_by: number;
    creator?: { id: number; name: string };
    items?: ControlPlanItem[];
    items_count?: number;
    created_at: string;
    updated_at: string;
}

// === Ҳоким ёрдамчилари ===

export type HyDirection = 'iqtisodiyot' | 'qurilish' | 'qishloq' | 'ijtimoiy' | 'madaniyat' | 'yoshlar' | 'boshqa';
export type HyAssignmentStatus = 'planned' | 'in_progress' | 'done' | 'cancelled';

export interface HyAssignment {
    id: number;
    hokim_yordamchisi_id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    status: HyAssignmentStatus;
    result_notes: string | null;
    created_by: number;
    creator?: { id: number; name: string } | null;
    created_at: string;
}

export interface HokimYordamchisi {
    id: number;
    hokimlik_id: number;
    user_id: number | null;
    full_name_cyr: string;
    phone: string | null;
    direction: HyDirection;
    mahalla_id: number | null;
    start_date: string;
    end_date: string | null;
    is_active: boolean;
    notes: string | null;
    created_by: number;
    user?: { id: number; name: string } | null;
    mahalla?: { id: number; name_cyr: string } | null;
    creator?: { id: number; name: string } | null;
    assignments?: HyAssignment[];
    assignments_count?: number;
    created_at: string;
    updated_at: string;
}

// === Ёшлар етакчилари ===

export type YyEventType = 'sport' | 'talim' | 'manaviyat' | 'ish' | 'boshqa';

export interface YyEvent {
    id: number;
    yoshlar_yetakchisi_id: number;
    event_type: YyEventType;
    title: string;
    description: string | null;
    event_date: string;
    participants_count: number;
    created_by: number;
    creator?: { id: number; name: string } | null;
}

export interface YoshlarYetakchisi {
    id: number;
    hokimlik_id: number;
    user_id: number | null;
    mahalla_id: number | null;
    full_name_cyr: string;
    phone: string | null;
    birth_date: string | null;
    start_date: string;
    end_date: string | null;
    is_active: boolean;
    notes: string | null;
    created_by: number;
    user?: { id: number; name: string } | null;
    mahalla?: { id: number; name_cyr: string } | null;
    creator?: { id: number; name: string } | null;
    events?: YyEvent[];
    events_count?: number;
    created_at: string;
    updated_at: string;
}

// === Murojaatlar (Citizen Appeals) ===

export type AppealStatus = 'draft' | 'submitted' | 'triaged' | 'routed' | 'in_review' | 'decided' | 'completed' | 'closed' | 'reopened';
export type AppealPriority = 'low' | 'normal' | 'high' | 'urgent';
export type AppealSource = 'web' | 'telegram' | 'voice' | 'paper' | 'meeting';
export type DecisionType = 'approve' | 'reject' | 'partial' | 'escalate' | 'info';

export interface AppealCategory {
    id: number;
    parent_id: number | null;
    code: string;
    name_cyr: string;
    name_lat: string | null;
    default_sla_hours: number;
    default_route_type: string | null;
    icon: string | null;
    children?: AppealCategory[];
}

export interface AppealAssignment {
    id: number;
    appeal_id: number;
    assignee_type: 'council' | 'department' | 'user';
    assignee_id: number;
    assigned_by: number;
    reason: string | null;
    status: 'active' | 'completed' | 'transferred' | 'cancelled';
    assigned_at: string;
    assigner?: { id: number; name: string };
}

export interface CouncilDecision {
    id: number;
    appeal_id: number;
    council_id: number;
    meeting_date: string;
    decision_type: DecisionType;
    decision_text: string;
    voting_result: Record<string, unknown> | null;
    decided_at: string;
    decider?: { id: number; name: string };
    council?: { id: number; mahalla?: { name_cyr: string } };
}

export interface AppealDocument {
    id: number;
    appeal_id: number;
    file_path: string;
    original_name: string;
    document_type: string | null;
    file_size: number;
    uploader?: { id: number; name: string } | null;
    created_at: string;
}

export interface AppealComment {
    id: number;
    appeal_id: number;
    body: string;
    is_internal: boolean;
    author?: { id: number; name: string };
    created_at: string;
}

export interface AppealStatusHistoryItem {
    id: number;
    from_status: string | null;
    to_status: string;
    reason: string | null;
    changed_at: string;
    changer?: { id: number; name: string } | null;
}

export interface CitizenAppeal {
    id: number;
    uuid: string;
    hokimlik_id: number;
    mahalla_id: number | null;
    youth_meeting_id: number | null;
    applicant_name: string;
    applicant_phone: string | null;
    applicant_birth_date: string | null;
    applicant_address: string | null;
    body: string;
    amount: number | null;
    category_id: number | null;
    sub_category_id: number | null;
    priority: AppealPriority;
    status: AppealStatus;
    source: AppealSource;
    submitted_at: string | null;
    sla_due_at: string | null;
    created_at: string;
    category?: AppealCategory | null;
    sub_category?: AppealCategory | null;
    mahalla?: { id: number; name_cyr: string } | null;
    active_assignment?: AppealAssignment | null;
    decisions?: CouncilDecision[];
    documents?: AppealDocument[];
    comments?: AppealComment[];
    status_history?: AppealStatusHistoryItem[];
}

// === Yoshlar uchrashuvlari ===

export interface YouthMeeting {
    id: number;
    uuid: string;
    mahalla_id: number | null;
    chairman_id: number;
    meeting_date: string;
    meeting_time: string | null;
    location: string | null;
    participants_count: number;
    agenda: string | null;
    notes: string | null;
    ai_summary: string | null;
    status: 'planned' | 'completed' | 'cancelled';
    appeals_count?: number;
    mahalla?: { id: number; name_cyr: string } | null;
    chairman?: { id: number; name: string };
    appeals?: CitizenAppeal[];
}

// === Mahalla yettiligi ===

export type CouncilMemberRole = 'rais' | 'imom' | 'yoshlar' | 'ayollar' | 'posbon' | 'maktab' | 'soliq' | 'boshqa';

export interface CouncilMember {
    id: number;
    council_id: number;
    user_id: number | null;
    full_name: string;
    role: CouncilMemberRole;
    phone: string | null;
    is_active: boolean;
    user?: { id: number; name: string } | null;
}

export interface MahallaCouncil {
    id: number;
    mahalla_id: number;
    name: string;
    phone: string | null;
    is_active: boolean;
    members?: CouncilMember[];
    members_count?: number;
    mahalla?: { id: number; name_cyr: string } | null;
}

export interface ActivityLog {
    id: number;
    description: string;
    subject_type: string;
    subject_id: number;
    causer_id: number | null;
    causer?: { id: number; name: string } | null;
    properties?: {
        old?: Record<string, unknown>;
        attributes?: Record<string, unknown>;
        [key: string]: unknown;
    };
    created_at: string;
}

export interface TenantInfo {
    id: number;
    name_cyr: string;
    name_lat?: string;
    type: string; // viloyat / shahar / tuman
}

export interface TenantContext {
    current: TenantInfo | null;
    is_global: boolean;
    is_cross_tenant: boolean;
    available: TenantInfo[] | null;
}

export interface PageProps {
    auth: {
        user: User | null;
        roles: string[];
        permissions: string[];
    };
    tenant?: TenantContext | null;
    flash: {
        success: string | null;
        error: string | null;
    };
    [key: string]: unknown;
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
