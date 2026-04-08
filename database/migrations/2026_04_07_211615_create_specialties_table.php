<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->string('name_cyr', 255)->comment('Мутахассислик номи (Кирилл)');
            $table->string('name_lat', 255)->comment('Mutaxassislik nomi (Lotin)');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('name_cyr');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialties');
    }
};
