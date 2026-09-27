<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Department;
use App\Support\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Multi-tenant model uchun trait.
 *
 * Modelga qo'shilganda:
 *  - Global scope: faqat joriy tenant yozuvlari
 *  - Avtomatik fill: yangi yozuv yaratilganda hokimlik_id user'dan olinadi
 *  - hokimlik() relation
 *
 * Override qilish uchun: TENANT_COLUMN constanta (default: 'hokimlik_id')
 *
 * SOLID-O: model tenant-aware bo'lishi uchun ochiq kengaytma
 * DRY: har modelda qayta yozmaslik uchun
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Global scope — har query'da avtomatik filterlash
        static::addGlobalScope('tenant', function (Builder $builder) {
            $context = app(TenantContext::class);

            // Super-admin yoki "global" rejim — barchasini ko'radi
            if ($context->isGlobal()) {
                return;
            }

            $tenantId = $context->id();
            if ($tenantId === null) {
                return; // tenant yo'q (masalan CLI yoki seeder)
            }

            $column = static::tenantColumn();
            $builder->where($builder->qualifyColumn($column), $tenantId);
        });

        // Yaratilayotganda — avtomatik fill
        static::creating(function ($model) {
            $column = static::tenantColumn();
            if (! empty($model->{$column})) {
                return;
            }

            $context = app(TenantContext::class);
            if ($context->id() !== null) {
                $model->{$column} = $context->id();
            }
        });
    }

    public static function tenantColumn(): string
    {
        return defined(static::class.'::TENANT_COLUMN')
            ? static::TENANT_COLUMN
            : 'hokimlik_id';
    }

    public function hokimlik(): BelongsTo
    {
        return $this->belongsTo(Department::class, static::tenantColumn());
    }

    /** Tenant filterini chetlab o'tish (super-admin reportlari uchun). */
    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
