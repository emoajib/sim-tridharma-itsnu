<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_proposal_kegiatan', function (Blueprint $table) {
            $table->enum('jenis_proposal', ['Ormawa', 'HIMA'])->after('id');
            $table->foreignId('prodi_id')->nullable()->after('ormawa_id')->constrained('m_prodi')->nullOnDelete();
            $table->enum('status_hima', ['Draft', 'Review_Kaprodi', 'Review_Dekan', 'Approved', 'Rejected'])->nullable()->after('status_kegiatan');
        });
    }

    public function down(): void
    {
        Schema::table('trx_proposal_kegiatan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prodi_id');
            $table->dropColumn(['jenis_proposal', 'status_hima']);
        });
    }
};
