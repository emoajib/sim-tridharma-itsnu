<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spmi_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('tahap'); // Penetapan, Pelaksanaan, Evaluasi, Pengendalian, Peningkatan (PPEPP)
            $table->string('kategori'); // Akademik, Non-Akademik
            $table->string('nama_siklus');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->integer('persentase_selesai')->default(0);
            $table->string('status')->default('on_progress'); // pending, on_progress, completed, overdue
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spmi_cycles');
    }
};
