<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('singkatan')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_base_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('knowledge_base_categories')->nullOnDelete();
            $table->string('judul');
            $table->string('sumber')->nullable();
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->integer('page_count')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('knowledge_base_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('knowledge_base_documents')->cascadeOnDelete();
            $table->integer('chunk_index');
            $table->longText('content');
            $table->json('embedding')->nullable();
            $table->timestamps();

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_chunks');
        Schema::dropIfExists('knowledge_base_documents');
        Schema::dropIfExists('knowledge_base_categories');
    }
};
