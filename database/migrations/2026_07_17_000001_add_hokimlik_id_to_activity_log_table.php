<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit журнали tenant-aware бўлиши учун hokimlik_id устуни.
 * Ёзувда TenantContext дан автоматик тўлдирилади, ўқишда tenant бўйича
 * фильтрланади (App\Models\Activity global scope) — cross-tenant маълумот
 * оқиб чиқишининг олдини олади.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->uuid('hokimlik_id')->nullable()->after('causer_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['hokimlik_id']);
            $table->dropColumn('hokimlik_id');
        });
    }
};
