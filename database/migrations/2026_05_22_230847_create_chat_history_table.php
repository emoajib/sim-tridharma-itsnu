<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

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
        // 50. trx_chat_history - Menyimpan riwayat percakapan Dosen dengan Chatbot RAG untuk analitik LPM
        Schema::create('trx_chat_history', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $blueprint->text('question');
            $blueprint->text('answer');
            $blueprint->json('sources')->nullable(); // Citasi dokumen yang digunakan
            $blueprint->decimal('max_similarity', 5, 4)->default(0); // Skor relevansi tertinggi
            $blueprint->string('mode')->default('sentence-only'); // qa-extractive, sentence-only, no-context
            $blueprint->timestamps();

            $blueprint->index('user_id');
            $blueprint->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_chat_history');
    }
};
