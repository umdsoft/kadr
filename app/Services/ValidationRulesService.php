<?php

declare(strict_types=1);

namespace App\Services;

use App\Validators\NoAbbreviationValidator;
use App\Validators\NoInitialsValidator;

/**
 * DRY: Барча валидация қоидалари битта жойда.
 * FormRequest, Import, API — ҳаммаси шу сервисдан фойдаланади.
 */
class ValidationRulesService
{
    /**
     * Рухсат этилган қариндошлик турлари (ENUM).
     *
     * @var array<string>
     */
    public const RELATIONSHIP_TYPES = [
        'Отаси', 'Онаси', 'Опаси', 'Синглиси', 'Акаси', 'Укаси',
        'Турмуш ўртоғи', 'Ўғли', 'Қизи', 'Қайнотаси', 'Қайнонаси',
        'Қайнукаси', 'Қайнсинглиси', 'Невараси', 'Келини', 'Куёви',
    ];

    /**
     * Рухсат этилган маълумот даражалари (ENUM).
     *
     * @var array<string>
     */
    public const EDUCATION_LEVELS = [
        'олий', 'тугалланмаган олий', 'ўрта махсус', 'ўрта',
    ];

    /**
     * Employee yaratish/yangilash uchun validatsiya qoidalari.
     *
     * @param  int|null  $excludeId  Yangilashda joriy employee ID (unique tekshiruv uchun)
     * @return array<string, mixed>
     */
    public function employeeRules(?int $excludeId = null): array
    {
        $uniqueJshshir = 'unique:employees,jshshir';
        if ($excludeId) {
            $uniqueJshshir .= ",{$excludeId}";
        }

        return [
            // 1-блок: Сарлавҳа
            'last_name_cyr' => ['required', 'string', 'max:50', new NoAbbreviationValidator],
            'first_name_cyr' => ['required', 'string', 'max:50'],
            'middle_name_cyr' => ['required', 'string', 'max:50'],
            'last_name_lat' => ['nullable', 'string', 'max:50'],
            'first_name_lat' => ['nullable', 'string', 'max:50'],
            'middle_name_lat' => ['nullable', 'string', 'max:50'],
            'current_position' => ['required', 'string', new NoAbbreviationValidator],
            'position_start_date' => ['required', 'date'],
            'photo_path' => ['nullable', 'string', 'max:255'],

            // 2-блок: Шахсий маълумотлар
            'birth_date' => ['required', 'date', 'before:-16 years'],
            'birth_place' => ['required', 'string', 'max:255', new NoAbbreviationValidator],
            'birth_region_id' => ['required', 'integer', 'exists:regions,id'],
            'birth_district_id' => ['required', 'integer', 'exists:districts,id'],
            'nationality' => ['required', 'string', 'max:50'],
            'party_affiliation' => ['required', 'string', 'max:100'],
            'education_level' => ['required', 'in:'.implode(',', self::EDUCATION_LEVELS)],
            'education_completion' => ['required', 'string', new NoAbbreviationValidator],
            'specialty_by_education' => ['required', 'string', 'max:255'],
            'academic_degree' => ['required', 'string', 'max:100'],
            'academic_title' => ['required', 'string', 'max:100'],
            'foreign_languages' => ['required', 'string'],
            'state_awards' => ['required', 'string', new NoAbbreviationValidator],
            'elected_body_member' => ['required', 'string'],

            // Махфий
            'jshshir' => ['required', 'string', 'size:14', $uniqueJshshir],
            'passport_series' => ['required', 'string', 'size:2'],
            'passport_number' => ['required', 'string', 'size:7'],

            // Хизмат
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
        ];
    }

    /**
     * Меҳнат фаолияти ёзуви учун валидация қоидалари.
     *
     * @return array<string, mixed>
     */
    public function workHistoryRules(): array
    {
        return [
            'work_history' => ['required', 'array', 'min:1'],
            'work_history.*.start_year' => ['required', 'integer', 'min:1950', 'max:' . date('Y')],
            'work_history.*.end_year' => ['nullable', 'integer', 'min:1950', 'max:' . date('Y')],
            'work_history.*.organization_full' => ['required', 'string', new NoAbbreviationValidator],
            'work_history.*.position_full' => ['required', 'string', new NoAbbreviationValidator],
            'work_history.*.order_number' => ['nullable', 'string', 'max:50'],
            'work_history.*.order_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Яқин қариндошлар ёзуви учун валидация қоидалари.
     *
     * @return array<string, mixed>
     */
    public function relativesRules(): array
    {
        return [
            'relatives' => ['required', 'array', 'min:1'],
            'relatives.*.relationship_type' => ['required', 'in:' . implode(',', self::RELATIONSHIP_TYPES)],
            'relatives.*.full_name_cyr' => ['required', 'string', 'max:255', new NoAbbreviationValidator, new NoInitialsValidator],
            'relatives.*.birth_year' => ['required', 'integer', 'min:1920', 'max:' . date('Y')],
            'relatives.*.birth_place' => ['required', 'string', 'max:255', new NoAbbreviationValidator],
            'relatives.*.is_deceased' => ['required', 'boolean'],
            'relatives.*.deceased_year' => ['nullable', 'required_if:relatives.*.is_deceased,true', 'integer', 'min:1950', 'max:' . date('Y')],
            'relatives.*.workplace_and_position' => ['required_if:relatives.*.is_deceased,false', 'string', new NoAbbreviationValidator],
            'relatives.*.former_position' => ['required_if:relatives.*.is_deceased,true', 'nullable', 'string'],
            'relatives.*.residence_full' => ['required', 'string', new NoAbbreviationValidator],
        ];
    }
}
