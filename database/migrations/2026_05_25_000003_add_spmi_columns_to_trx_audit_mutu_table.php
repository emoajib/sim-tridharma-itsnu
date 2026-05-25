<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_audit_mutu', function (Blueprint $table) {
            // Relasi ke standar mutu
            $table->foreignId('standar_mutu_id')->nullable()->constrained('m_standar_mutu')->nullOnDelete();

            // Severity
            $table->enum('severity', ['ringan', 'sedang', 'berat', 'kritis'])->default('ringan')->after('status');

            // PIC assignment
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('auditor_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Timeline
            $table->date('deadline_tindak_lanjut')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Evidence & verification
            $table->string('evidence_file', 255)->nullable();
            $table->text('verification_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // Immutability
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();

            // Indexes for performance
            $table->index(['standar_mutu_id', 'severity']);
            $table->index(['pic_user_id', 'status']);
            $table->index(['deadline_tindak_lanjut', 'status']);
            $table->index(['prodi_id', 'periode_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('trx_audit_mutu', function (Blueprint $table) {
            $table->dropConstrainedForeignId('standar_mutu_id');
            $table->dropConstrainedForeignId('pic_user_id');
            $table->dropConstrainedForeignId('auditor_user_id');
            $table->dropConstrainedForeignId('verified_by');

            $table->dropColumn([
                'severity',
                'deadline_tindak_lanjut',
                'closed_at',
                'evidence_file',
                'verification_note',
                'verified_at',
                'is_locked',
                'locked_at',
            ]);

            $table->dropIndex(['standar_mutu_id', 'severity']);
            $table->dropIndex(['pic_user_id', 'status']);
            $table->dropIndex(['deadline_tindak_lanjut', 'status']);
            $table->dropIndex(['prodi_id', 'periode_id', 'status']);
        });
    }
};
