<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\UserObserver;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy(UserObserver::class)]
#[Fillable(['name', 'login', 'email', 'password', 'department_id', 'position_id', 'organization_id'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use HasUuids;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** Ташкилот фойдаланувчиси (tashkilot-admin / tashkilot-xodimi) учун. */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Foydalanuvchi tegishli bo'lgan tenant (top-level hokimlik) ID si.
     * - Ташкилот фойдаланувчиси → organization.hokimlik_id
     * - Department parent_id IS NULL → o'zi tenant
     * - Department parent_id IS NOT NULL → parent tenant
     */
    public function getHokimlikIdAttribute(): ?string
    {
        // Ташкилот фойдаланувчиси — тенант ташкилот орқали аниқланади
        if ($this->organization_id !== null) {
            return $this->organization?->hokimlik_id;
        }

        $dept = $this->department;
        if (! $dept) {
            return null;
        }

        // 3 darajali daraxt (hokimlik → kompleks → boshqarma) — ildizgacha ko'tariladi.
        return $dept->rootId();
    }

    public function isFromTenant(?string $hokimlikId): bool
    {
        return $hokimlikId !== null && $this->hokimlik_id === $hokimlikId;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'department_id'])
            ->logOnlyDirty();
    }
}
