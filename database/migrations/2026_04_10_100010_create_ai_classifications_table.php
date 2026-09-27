<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_classifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appeal_id')->constrained('citizen_appeals')->cascadeOnDelete();
            $table->string('model_name', 100);
            $table->foreignUuid('category_predicted')->nullable()->constrained('appeal_categories')->nullOnDelete();
            $table->foreignUuid('sub_category_predicted')->nullable()->constrained('appeal_categories')->nullOnDelete();
            $table->string('priority_predicted', 20)->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->json('similar_appeals')->nullable()->comment('Top-K similar appeal IDs');
            $table->text('reasoning')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('appeal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_classifications');
    }
};
