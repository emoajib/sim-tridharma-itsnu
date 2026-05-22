<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 46. m_indikator_iku - Master 8 IKU Kemdiktisaintek
        Schema::create('m_indikator_iku', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('kode_iku')->unique(); // IKU 1, IKU 2, etc.
            $blueprint->string('nama_iku');
            $blueprint->text('deskripsi')->nullable();
            $blueprint->string('satuan')->default('persentase');
            $blueprint->decimal('bobot', 5, 2)->default(0);
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });

        // 47. trx_cascading_iku - Target kinerja per unit (Rektor -> Dekan -> Kaprodi)
        Schema::create('trx_cascading_iku', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('iku_id')->constrained('m_indikator_iku')->onDelete('cascade');
            $blueprint->foreignId('periode_id')->constrained('m_periode_akademik')->onDelete('cascade');
            $blueprint->string('unit_type'); // Rektorat, Fakultas, Prodi
            $blueprint->unsignedBigInteger('unit_id'); // ID dari m_fakultas atau m_prodi
            $blueprint->decimal('target', 10, 2);
            $blueprint->decimal('capaian', 10, 2)->default(0);
            $blueprint->text('catatan')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['unit_type', 'unit_id']);
        });

        // 48. trx_usulan_rkat - Draf usulan Dosen/Prodi (berisi RAB dan tagging kinerja)
        Schema::create('trx_usulan_rkat', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('prodi_id')->constrained('m_prodi')->onDelete('cascade');
            $blueprint->foreignId('periode_id')->constrained('m_periode_akademik')->onDelete('cascade');
            $blueprint->string('judul_kegiatan');
            $blueprint->text('deskripsi_kegiatan')->nullable();
            $blueprint->decimal('estimasi_biaya', 15, 2);
            $blueprint->foreignId('iku_id')->nullable()->constrained('m_indikator_iku');
            $blueprint->foreignId('indikator_akreditasi_id')->nullable()->constrained('m_indikator_akreditasi');
            $blueprint->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'revised'])->default('draft');
            $blueprint->text('komentar_reviewer')->nullable();
            $blueprint->foreignId('user_id')->constrained('users'); // Pengusul
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });

        // 49. trx_rkat_approval_log - Audit trail persetujuan RKAT
        Schema::create('trx_rkat_approval_log', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('rkat_id')->constrained('trx_usulan_rkat')->onDelete('cascade');
            $blueprint->foreignId('user_id')->constrained('users'); // Reviewer
            $blueprint->string('action'); // approve, reject, revise
            $blueprint->text('keterangan')->nullable();
            $blueprint->timestamps();
        });

        // 50. trx_rkat_pagu - Alokasi batas atas anggaran per unit kerja
        Schema::create('trx_rkat_pagu', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('periode_id')->constrained('m_periode_akademik')->onDelete('cascade');
            $blueprint->string('unit_type'); // Rektorat, Fakultas, Prodi
            $blueprint->unsignedBigInteger('unit_id');
            $blueprint->decimal('pagu_total', 15, 2);
            $blueprint->decimal('terpakai', 15, 2)->default(0);
            $blueprint->timestamps();

            $blueprint->index(['unit_type', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_rkat_pagu');
        Schema::dropIfExists('trx_rkat_approval_log');
        Schema::dropIfExists('trx_usulan_rkat');
        Schema::dropIfExists('trx_cascading_iku');
        Schema::dropIfExists('m_indikator_iku');
    }
};
