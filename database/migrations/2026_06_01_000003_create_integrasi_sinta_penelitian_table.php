<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrasi_sinta_penelitian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->nullable()->constrained('m_dosen')->nullOnDelete();
            $table->foreignId('penelitian_id')->nullable()->constrained('trx_penelitian')->nullOnDelete();
            $table->text('judul');
            $table->string('tahun', 10)->nullable();
            $table->string('skema', 100)->nullable();
            $table->decimal('jumlah_dana', 15)->nullable();
            $table->json('data_dari_sinta');
            $table->string('status_sinkron', 30)->default('pending');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dosen_id', 'status_sinkron']);

            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText('judul');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrasi_sinta_penelitian');
    }
};
