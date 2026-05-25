<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_audit_mutu_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_mutu_id')->constrained('trx_audit_mutu')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('field', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('action', 50);
            $table->timestamps();

            $table->index('audit_mutu_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_audit_mutu_histories');
    }
};
