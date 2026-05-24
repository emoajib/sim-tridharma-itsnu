<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliation_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 30);
            $table->jsonb('source_data');
            $table->string('target_table', 50)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('match_field', 30)->nullable();
            $table->string('match_value')->nullable();
            $table->decimal('similarity_score', 5, 2);
            $table->string('confidence', 10);
            $table->string('status', 20)->default('pending');
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->nullOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('m_dosen')->nullOnDelete();
            $table->string('suggested_by', 20)->default('system');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'source_type']);
            $table->index(['prodi_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_suggestions');
    }
};
