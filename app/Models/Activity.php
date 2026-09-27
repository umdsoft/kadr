<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * UUID primary key + tenant-aware audit журнали.
 *
 * - Ёзувда: hokimlik_id жорий TenantContext дан автоматик тўлдирилади.
 * - Ўқишда: global scope tenant бўйича фильтрлайди (cross-tenant/viloyat/super
 *   бундан мустасно) — бир ҳокимлик админи бошқа ҳокимлик аудитини кўрмайди.
 *
 * Диққат: config/activitylog.php да activity_model = App\Models\Activity,
 * шунинг учун барча ёзувлар шу модел орқали ўтади. Ўқиш жойлари ҳам
 * (AuditController, DashboardController) шу моделни ишлатиши шарт, акс ҳолда
 * Spatie нинг ота классига global scope қўлланмайди.
 */
class Activity extends SpatieActivity
{
    use HasUuids;

    protected static function booted(): void
    {
        // Ёзувда — жорий tenant'ни белгилаш (insert'га global scope таъсир қилмайди).
        static::creating(function (self $activity): void {
            if ($activity->getAttribute('hokimlik_id') === null) {
                $activity->setAttribute('hokimlik_id', app(TenantContext::class)->id());
            }
        });

        // Ўқишда — tenant бўйича фильтр.
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);

            // Global rejim (super-admin / viloyat-admin) — барчасини кўради.
            if ($context->isGlobal()) {
                return;
            }

            $tenantId = $context->id();
            if ($tenantId === null) {
                // CLI / seeder / tenant аниқланмаган — фильтрсиз (масалан activitylog:clean).
                return;
            }

            $builder->where($builder->qualifyColumn('hokimlik_id'), $tenantId);
        });
    }
}
