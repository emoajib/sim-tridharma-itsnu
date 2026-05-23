<?php

namespace Database\Factories;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeBaseDocumentFactory extends Factory
{
    protected $model = KnowledgeBaseDocument::class;

    public function definition(): array
    {
        return [
            'category_id' => KnowledgeBaseCategory::factory(),
            'judul' => fake()->sentence(),
            'sumber' => fake()->word(),
            'file_path' => fake()->filePath(),
            'file_size' => fake()->numberBetween(1000, 5000000),
            'page_count' => fake()->numberBetween(1, 500),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'error']),
        ];
    }
}
