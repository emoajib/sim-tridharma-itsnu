<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only run on PostgreSQL with pgvector extension
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        try {
            DB::statement('SELECT 1 FROM pg_extension WHERE extname = \'vector\'');
        } catch (\Exception $e) {
            return; // pgvector not installed, skip
        }

        $hasVector = DB::select('SELECT 1 FROM pg_extension WHERE extname = \'vector\'');
        if (empty($hasVector)) {
            return; // pgvector not installed, skip
        }

        // Multilingual-e5-small has 384 dimensions
        DB::statement('ALTER TABLE knowledge_base_chunks ALTER COLUMN embedding TYPE vector(384) USING embedding::text::vector(384)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE knowledge_base_chunks ALTER COLUMN embedding TYPE json USING embedding::text::json');
    }
};
