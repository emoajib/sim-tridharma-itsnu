<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_rtm', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->date('tanggal_rapat');
            $table->text('agenda')->nullable();
            $table->text('notulen')->nullable();
            $table->string('file_notulen', 255)->nullable();
            $table->foreignId('dipimpin_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('trx_rtm_action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtm_id')->constrained('trx_rtm')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('deadline')->nullable();
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->text('hasil')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['rtm_id', 'status']);
            $table->index(['pic_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_rtm_action_items');
        Schema::dropIfExists('trx_rtm');
    }
};
