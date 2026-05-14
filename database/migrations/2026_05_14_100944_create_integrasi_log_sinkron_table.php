<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrasi_log_sinkron', function (Blueprint $table) {
            $table->id();
            $table->string('sumber', 50);
            $table->string('jenis_data', 100);
            $table->string('status', 20);
            $table->integer('jumlah_ditarik')->default(0);
            $table->integer('jumlah_konflik')->default(0);
            $table->integer('jumlah_diperbarui')->default(0);
            $table->text('detail')->nullable();
            $table->timestamp('mulai_pada');
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrasi_log_sinkron');
    }
};
