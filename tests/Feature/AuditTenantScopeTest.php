<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Department;
use App\Support\Tenant\TenantContext;

/**
 * M2 — audit jurnali tenant-aware. Bir hokimlik admini boshqa hokimlik
 * faoliyat jurnalini ko'ra olmaydi (cross-tenant PII oqishi yopiladi).
 */
beforeEach(function () {
    $this->rootA = Department::create(['name_cyr' => 'Ҳокимлик A', 'name_lat' => 'Hokimlik A', 'code' => 'A', 'type' => 'hokimlik']);
    $this->rootB = Department::create(['name_cyr' => 'Ҳокимлик B', 'name_lat' => 'Hokimlik B', 'code' => 'B', 'type' => 'hokimlik']);
});

function logActivityInTenant(?string $tenantId, bool $global, string $description): void
{
    app(TenantContext::class)->set($tenantId, $global);

    $activity = new Activity;
    $activity->log_name = 'default';
    $activity->description = $description;
    $activity->save();
}

it('yozuvda hokimlik_id joriy tenantdan avtomatik to\'ldiriladi', function () {
    logActivityInTenant($this->rootA->id, false, 'A hodisa');

    app(TenantContext::class)->set(null, true); // global — filtrsiz o'qish
    $activity = Activity::first();

    expect($activity->getAttribute('hokimlik_id'))->toBe($this->rootA->id);
});

it('tenant admini faqat o\'z hokimligi audit yozuvlarini ko\'radi', function () {
    logActivityInTenant($this->rootA->id, false, 'A hodisa');
    logActivityInTenant($this->rootB->id, false, 'B hodisa');

    // A tenantida o'qish — faqat A ko'rinadi
    app(TenantContext::class)->set($this->rootA->id, false);
    $visible = Activity::get();

    expect($visible)->toHaveCount(1)
        ->and($visible->first()->description)->toBe('A hodisa');
});

it('global rejim (super-admin) barcha tenant yozuvlarini ko\'radi', function () {
    logActivityInTenant($this->rootA->id, false, 'A hodisa');
    logActivityInTenant($this->rootB->id, false, 'B hodisa');

    app(TenantContext::class)->set(null, true);

    expect(Activity::count())->toBe(2);
});
