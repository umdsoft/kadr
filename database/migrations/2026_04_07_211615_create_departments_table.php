<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('name_cyr', 255)->comment('Бўлим номи (Кирилл)');
            $table->string('name_lat', 255)->comment('Bo\'lim nomi (Lotin)');
            $table->string('code', 20)->nullable()->unique()->comment('Ички код');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('name_cyr');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
