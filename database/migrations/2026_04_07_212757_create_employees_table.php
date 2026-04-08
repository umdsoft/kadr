<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TT бўлим 5А.1 — employees жадвали.
 * 1-БЛОК (Сарлавҳа) + 2-БЛОК (Шахсий маълумотлар) майдонлари.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // ===== 1-БЛОК: Сарлавҳа =====
            $table->string('last_name_cyr', 50)->comment('Фамилияси (Кирилл)');
            $table->string('first_name_cyr', 50)->comment('Исми (Кирилл)');
            $table->string('middle_name_cyr', 50)->comment('Отасининг исми (Кирилл)');
            $table->string('last_name_lat', 50)->nullable()->comment('Фамилияси (Лотин) — автотранслит');
            $table->string('first_name_lat', 50)->nullable()->comment('Исми (Лотин)');
            $table->string('middle_name_lat', 50)->nullable()->comment('Отасининг исми (Лотин)');
            $table->text('current_position')->comment('Ҳозирги лавозими — тўлиқ');
            $table->date('position_start_date')->comment('Лавозимга тайинланган сана');
            $table->string('photo_path', 255)->nullable()->comment('3×4 расм (JPG/PNG, max 2 MB)');

            // ===== 2-БЛОК: Шахсий маълумотлар =====
            $table->date('birth_date')->comment('Туғилган санаси (мин. 16 ёш)');
            $table->string('birth_place', 255)->comment('Туғилган жойи — тўлиқ, қисқартиришсиз');
            $table->foreignId('birth_region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('birth_district_id')->constrained('districts')->restrictOnDelete();
            $table->string('nationality', 50)->comment('Миллати — каталогдан');
            $table->string('party_affiliation', 100)->default('йўқ')->comment('Партиявийлиги');
            $table->enum('education_level', [
                'олий', 'тугалланмаган олий', 'ўрта махсус', 'ўрта',
            ])->comment('Маълумоти даражаси');
            $table->text('education_completion')->comment('Қаерни тамомлаган (йил, ОТМ, шакли)');
            $table->string('specialty_by_education', 255)->comment('Маълумоти бўйича мутахассислиги');
            $table->string('academic_degree', 100)->default('йўқ')->comment('Илмий даражаси');
            $table->string('academic_title', 100)->default('йўқ')->comment('Илмий унвони');
            $table->text('foreign_languages')->default('йўқ')->comment('Чет тиллари (мукаммал)');
            $table->text('state_awards')->default('тақдирланмаган')->comment('Давлат мукофотлари');
            $table->text('elected_body_member')->default('йўқ')->comment('Сайланадиган органлар аъзолиги');

            // ===== Махфий (шифрланган) =====
            $table->string('jshshir', 14)->unique()->comment('ЖШШИР — шифрланган');
            $table->string('passport_series', 2)->comment('Паспорт серияси — шифрланган');
            $table->string('passport_number', 7)->comment('Паспорт рақами — шифрланган');

            // ===== Хизмат майдонлари =====
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('position_id')->constrained()->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ===== Индекслар =====
            $table->index('last_name_cyr');
            $table->index('birth_date');
            $table->index('department_id');
            $table->index('position_id');
            // FULLTEXT фақат MySQL да ишлайди (SQLite test да ўтказиб юборилади)
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['last_name_cyr', 'first_name_cyr', 'middle_name_cyr']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
