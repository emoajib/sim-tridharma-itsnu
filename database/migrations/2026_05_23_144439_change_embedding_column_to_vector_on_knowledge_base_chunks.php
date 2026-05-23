<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Multilingual-e5-small has 384 dimensions
        // We cast to text first, then clean any JSON brackets if needed, but text to vector usually works if formatted correctly
        DB::statement('ALTER TABLE knowledge_base_chunks ALTER COLUMN embedding TYPE vector(384) USING embedding::text::vector(384)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE knowledge_base_chunks ALTER COLUMN embedding TYPE json USING embedding::text::json');
    }
};
