// Tizim bo'yicha takrorlanuvchi label/color konstantalari
// DRY: barcha sahifalar shu yerdan oladi

export const ROLE_LABELS: Record<string, string> = {
    'super-admin': 'Супер Админ',
    'hokim-maslahatchisi': 'Ҳоким маслаҳатчиси',
    'hokim-orinbosari': 'Ҳоким ўринбосари',
    'kotibyat-mudiri': 'Котибият мудири',
    'axborot-tahlil': 'Ахборот таҳлил гуруҳи',
    'mutaxassis': 'Мутахассис',
    'kadrlar-xodimi': 'Кадрлар ходими',
};

export const EXECUTION_STATUS_LABELS: Record<string, string> = {
    not_started: 'Бажарилмаган',
    in_progress: 'Бажарилмоқда',
    completed: 'Бажарилган',
    overdue: 'Муддати ўтган',
};

export const EXECUTION_STATUS_BADGE_COLORS: Record<string, string> = {
    not_started: 'bg-gray-100 text-gray-600',
    in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    overdue: 'bg-red-100 text-red-700',
};

export const EXECUTION_STATUS_TEXT_COLORS: Record<string, string> = {
    not_started: 'text-gray-500',
    in_progress: 'text-blue-600',
    completed: 'text-green-700 font-bold',
    overdue: 'text-red-600 font-bold',
};

export const EXECUTION_STATUS_BUTTON_COLORS: Record<string, string> = {
    not_started: 'bg-gray-600 text-white border-gray-600',
    in_progress: 'bg-blue-600 text-white border-blue-600',
    completed: 'bg-green-600 text-white border-green-600',
    overdue: 'bg-red-600 text-white border-red-600',
};

export const PLAN_STATUS_LABELS: Record<string, string> = {
    active: 'Фаол',
    completed: 'Бажарилган',
    archived: 'Архив',
};

export const PLAN_STATUS_COLORS: Record<string, string> = {
    active: 'bg-green-100 text-green-700',
    completed: 'bg-blue-100 text-blue-700',
    archived: 'bg-gray-100 text-gray-600',
};

export const PRIORITY_LABELS: Record<string, string> = {
    low: 'Паст',
    medium: 'Ўрта',
    high: 'Юқори',
    urgent: 'Шошилинч',
};

export const UZBEK_MONTHS = [
    'январ', 'феврал', 'март', 'апрел', 'май', 'июн',
    'июл', 'август', 'сентябр', 'октябр', 'ноябр', 'декабр',
];

export const DEBOUNCE_DELAY = 300;

// ===== Hokim yordamchilari direction =====
export const HY_DIRECTION_LABELS: Record<string, string> = {
    iqtisodiyot: 'Иқтисодиёт',
    qurilish: 'Қурилиш',
    qishloq: 'Қишлоқ',
    ijtimoiy: 'Ижтимоий',
    madaniyat: 'Маданият',
    yoshlar: 'Ёшлар',
    boshqa: 'Бошқа',
};

// ===== HY assignment status =====
export const HY_ASSIGNMENT_STATUS_LABELS: Record<string, string> = {
    planned: 'Режалаштирилган',
    in_progress: 'Бажарилмоқда',
    done: 'Бажарилган',
    cancelled: 'Бекор қилинган',
};

export const HY_ASSIGNMENT_STATUS_COLORS: Record<string, string> = {
    planned: 'bg-gray-100 text-gray-700',
    in_progress: 'bg-blue-100 text-blue-700',
    done: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};

// ===== Yoshlar yetakchilari event types =====
export const YY_EVENT_TYPE_LABELS: Record<string, string> = {
    sport: 'Спорт',
    talim: 'Таълим',
    manaviyat: 'Маънавият',
    ish: 'Иш',
    boshqa: 'Бошқа',
};

// ===== Murojaatlar — appeal status =====
export const APPEAL_STATUS_LABELS: Record<string, string> = {
    draft: 'Қоралама',
    submitted: 'Юборилди',
    triaged: 'Сараланди',
    routed: 'Йўналтирилди',
    in_review: 'Кўриб чиқилмоқда',
    decided: 'Қарор қабул қилинди',
    completed: 'Якунланди',
    closed: 'Ёпилди',
    reopened: 'Қайта очилди',
};

export const APPEAL_STATUS_COLORS: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    submitted: 'bg-blue-100 text-blue-700',
    triaged: 'bg-indigo-100 text-indigo-700',
    routed: 'bg-purple-100 text-purple-700',
    in_review: 'bg-yellow-100 text-yellow-700',
    decided: 'bg-cyan-100 text-cyan-700',
    completed: 'bg-green-100 text-green-700',
    closed: 'bg-gray-100 text-gray-500',
    reopened: 'bg-orange-100 text-orange-700',
};

export const APPEAL_PRIORITY_LABELS: Record<string, string> = {
    low: 'Паст',
    normal: 'Оддий',
    high: 'Юқори',
    urgent: 'Шошилинч',
};

export const APPEAL_PRIORITY_COLORS: Record<string, string> = {
    low: 'bg-gray-100 text-gray-600',
    normal: 'bg-blue-100 text-blue-700',
    high: 'bg-orange-100 text-orange-700',
    urgent: 'bg-red-100 text-red-700',
};

export const APPEAL_SOURCE_LABELS: Record<string, string> = {
    web: 'Веб',
    telegram: 'Телеграм',
    voice: 'Овозли',
    paper: 'Қоғоз',
    meeting: 'Учрашув',
};

// ===== Council decision types =====
export const DECISION_TYPE_LABELS: Record<string, string> = {
    approve: 'Тасдиқланди',
    reject: 'Рад этилди',
    partial: 'Қисман тасдиқланди',
    escalate: 'Юқорига юборилди',
    info: 'Маълумот берилди',
};

export const DECISION_TYPE_COLORS: Record<string, string> = {
    approve: 'bg-green-100 text-green-700',
    reject: 'bg-red-100 text-red-700',
    partial: 'bg-yellow-100 text-yellow-700',
    escalate: 'bg-orange-100 text-orange-700',
    info: 'bg-blue-100 text-blue-700',
};

// ===== Council member roles =====
export const COUNCIL_ROLE_LABELS: Record<string, string> = {
    rais: 'Маҳалла раиси',
    imom: 'Имом-хатиб',
    yoshlar: 'Ёшлар етакчиси',
    ayollar: 'Аёллар етакчиси (АТЭМ)',
    posbon: 'Посбон',
    maktab: 'Мактаб вакили',
    soliq: 'Солиқ/Иқтисод вакили',
    boshqa: 'Бошқа',
};

// ===== Meeting status =====
export const MEETING_STATUS_LABELS: Record<string, string> = {
    planned: 'Режалаштирилган',
    completed: 'Ўтказилди',
    cancelled: 'Бекор қилинди',
};

export const MEETING_STATUS_COLORS: Record<string, string> = {
    planned: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-gray-100 text-gray-500',
};
