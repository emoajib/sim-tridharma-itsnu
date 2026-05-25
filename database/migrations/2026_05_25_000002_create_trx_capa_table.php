<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_capa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_mutu_id')->constrained('trx_audit_mutu')->cascadeOnDelete();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Root Cause Analysis
            $table->enum('root_cause_category', [
                'proses', 'sdm', 'sarana', 'keuangan', 'regulasi', 'eksternal', 'lainnya',
            ])->nullable();
            $table->text('root_cause_analysis')->nullable();

            // Corrective Action
            $table->text('corrective_action')->nullable();
            $table->date('corrective_deadline')->nullable();
            $table->date('corrective_completed_at')->nullable();
            $table->string('corrective_evidence_file', 255)->nullable();

            // Preventive Action
            $table->text('preventive_action')->nullable();
            $table->date('preventive_deadline')->nullable();
            $table->date('preventive_completed_at')->nullable();
            $table->string('preventive_evidence_file', 255)->nullable();

            // Workflow
            $table->enum('status', [
                'draft', 'open', 'in_progress', 'awaiting_verification',
                'verified', 'rejected', 'closed', 'archived',
            ])->default('open');

            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pic_user_id', 'status']);
            $table->index(['audit_mutu_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_capa');
    }
};
