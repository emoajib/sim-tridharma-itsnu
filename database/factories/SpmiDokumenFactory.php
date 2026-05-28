<?php

namespace Database\Factories;

use App\Models\SpmiDokumen;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpmiDokumenFactory extends Factory
{
    protected $model = SpmiDokumen::class;

    public function definition(): array
    {
        return [
            'kategori' => $this->faker->randomElement(['Akademik', 'Non-Akademik', 'Keuangan']),
            'nomor_dokumen' => $this->faker->unique()->numerify('DOC-#####'),
            'judul' => $this->faker->sentence(4),
            'deskripsi' => $this->faker->optional()->paragraph,
            'version' => 1,
            'status' => 'draft',
        ];
    }
}
