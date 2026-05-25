<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_spmi_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50);
            $table->string('nomor_dokumen', 50)->unique();
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('file_original_name', 255)->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('tanggal_berlaku')->nullable();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->enum('status', ['draft', 'review', 'approved', 'expired', 'archived'])->default('draft');
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('kategori');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_spmi_dokumen');
    }
};
