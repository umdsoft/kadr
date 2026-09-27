<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MARKAZIY SESSIYA (SSO) — `auth.sessions`.
 * Barcha tizimlar (xbt, mahalla, ...) SESSION_CONNECTION=auth bilan shu jadvalni
 * bo'lishadi → bir marta login, hamma tizimga (shared cookie + bir xil APP_KEY).
 * Prod'da Redis'ga o'tish mumkin (SESSION_DRIVER=redis) — bu jadval fallback.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        Schema::connection('auth')->create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        Schema::connection('auth')->dropIfExists('sessions');
    }
};
