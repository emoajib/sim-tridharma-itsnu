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
        // Step 1: Add columns as nullable (without constraints)
        Schema::table('spmi_cycles', function (Blueprint $table) {
            $table->bigInteger('prodi_id')->nullable();
            $table->bigInteger('periode_id')->nullable();
            $table->bigInteger('instrumen_id')->nullable();
            $table->index(['prodi_id', 'periode_id', 'tahap']);
        });

        // Step 2: Update existing records with default values
        $prodiId = DB::table('m_prodi')->value('id');
        $periodeId = DB::table('m_periode_akademik')->value('id');
        $instrumenId = DB::table('m_instrumen_akreditasi')->value('id');

        if ($prodiId && $periodeId) {
            DB::table('spmi_cycles')
                ->whereNull('prodi_id')
                ->update(['prodi_id' => $prodiId]);

            DB::table('spmi_cycles')
                ->whereNull('periode_id')
                ->update(['periode_id' => $periodeId]);
        }

        if ($instrumenId) {
            DB::table('spmi_cycles')
                ->whereNull('instrumen_id')
                ->update(['instrumen_id' => $instrumenId]);
        }

        // Step 3: Add foreign key constraints and set not null for prodi_id and periode_id
        Schema::table('spmi_cycles', function (Blueprint $table) {
            $table->foreign('prodi_id')->references('id')->on('m_prodi')->onDelete('cascade');
            $table->foreign('periode_id')->references('id')->on('m_periode_akademik')->onDelete('cascade');
            $table->foreign('instrumen_id')->references('id')->on('m_instrumen_akreditasi')->onDelete('set null');
        });

        // Set prodi_id and periode_id to not null
        Schema::table('spmi_cycles', function (Blueprint $table) {
            $table->bigInteger('prodi_id')->nullable(false)->change();
            $table->bigInteger('periode_id')->nullable(false)->change();
            // instrumen_id remains nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmi_cycles', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropForeign(['periode_id']);
            $table->dropForeign(['instrumen_id']);
            $table->dropIndex(['prodi_id', 'periode_id', 'tahap']);
            $table->dropColumn(['prodi_id', 'periode_id', 'instrumen_id']);
        });
    }
};
