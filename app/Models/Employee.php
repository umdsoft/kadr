<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string $uuid
 * @property string $last_name_cyr
 * @property string $first_name_cyr
 * @property string $middle_name_cyr
 * @property string|null $last_name_lat
 * @property string|null $first_name_lat
 * @property string|null $middle_name_lat
 * @property string $current_position
 * @property \Carbon\Carbon $position_start_date
 * @property string|null $photo_path
 * @property \Carbon\Carbon $birth_date
 * @property string $birth_place
 * @property int $birth_region_id
 * @property int $birth_district_id
 * @property string $nationality
 * @property string $party_affiliation
 * @property string $education_level
 * @property string $education_completion
 * @property string $specialty_by_education
 * @property string $academic_degree
 * @property string $academic_title
 * @property string $foreign_languages
 * @property string $state_awards
 * @property string $elected_body_member
 * @property string $jshshir
 * @property string $passport_series
 * @property string $passport_number
 * @property int $department_id
 * @property int $position_id
 * @property string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkHistory> $workHistory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Relative> $relatives
 */
class Employee extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        // 1-блок: Сарлавҳа
        'uuid',
        'last_name_cyr',
        'first_name_cyr',
        'middle_name_cyr',
        'last_name_lat',
        'first_name_lat',
        'middle_name_lat',
        'current_position',
        'position_start_date',
        'photo_path',
        // 2-блок: Шахсий маълумотлар
        'birth_date',
        'birth_place',
        'birth_region_id',
        'birth_district_id',
        'nationality',
        'party_affiliation',
        'education_level',
        'education_completion',
        'specialty_by_education',
        'academic_degree',
        'academic_title',
        'foreign_languages',
        'state_awards',
        'elected_body_member',
        // Махфий
        'jshshir',
        'passport_series',
        'passport_number',
        // Хизмат
        'department_id',
        'position_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'position_start_date' => 'date',
            'jshshir' => 'encrypted',
            'passport_series' => 'encrypted',
            'passport_number' => 'encrypted',
        ];
    }

    // ===== Муносабатлар =====

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function birthRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'birth_region_id');
    }

    public function birthDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'birth_district_id');
    }

    public function workHistory(): HasMany
    {
        return $this->hasMany(WorkHistory::class)->orderBy('sort_order');
    }

    public function relatives(): HasMany
    {
        return $this->hasMany(Relative::class);
    }

    // ===== Accessor лар =====

    /**
     * Тўлиқ Ф.И.Ш. (Кирилл).
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name_cyr} {$this->first_name_cyr} {$this->middle_name_cyr}");
    }

    // ===== Audit log =====

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'last_name_cyr', 'first_name_cyr', 'middle_name_cyr',
                'current_position', 'position_start_date',
                'department_id', 'position_id',
                'education_level', 'state_awards',
            ])
            ->logOnlyDirty();
    }
}
