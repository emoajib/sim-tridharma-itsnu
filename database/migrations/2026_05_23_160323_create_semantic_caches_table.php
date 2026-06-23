<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Vetted by AI - Manual Review Required by Senior Engineer/Manager
     */
    public function up(): void
    {
        Schema::create('semantic_caches', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->longText('answer');
            $table->json('sources')->nullable();
            $table->string('provider')->default('gemini');
            $table->integer('hit_count')->default(1);
            $table->timestamp('last_hit_at')->useCurrent();
            $table->timestamps();
        });

        // Only run on PostgreSQL with pgvector extension
        if (DB::getDriverName() === 'pgsql') {
            try {
                DB::statement('SELECT 1 FROM pg_extension WHERE extname = \'vector\'');

                $hasVector = DB::select('SELECT 1 FROM pg_extension WHERE extname = \'vector\'');
                if (!empty($hasVector)) {
                    DB::statement('ALTER TABLE semantic_caches ADD COLUMN question_vector vector(384)');
                    DB::statement('CREATE INDEX semantic_cache_vector_idx ON semantic_caches USING hnsw (question_vector vector_cosine_ops)');
                }
            } catch (\Exception $e) {
                // pgvector not installed, skip
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semantic_caches');
    }
};
