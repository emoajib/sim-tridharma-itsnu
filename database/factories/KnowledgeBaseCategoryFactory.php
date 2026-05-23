<?php

namespace Database\Factories;

use App\Models\KnowledgeBaseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeBaseCategoryFactory extends Factory
{
    protected $model = KnowledgeBaseCategory::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->words(2, true),
            'singkatan' => fake()->unique()->lexify('???'),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
