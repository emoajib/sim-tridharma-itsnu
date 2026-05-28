<?php

namespace Database\Factories;

use App\Models\StandarMutu;
use Illuminate\Database\Eloquent\Factories\Factory;

class StandarMutuFactory extends Factory
{
    protected $model = StandarMutu::class;

    public function definition(): array
    {
        return [
            'kategori' => $this->faker->randomElement(['Akademik', 'Non-Akademik']),
            'kode_standar' => $this->faker->unique()->numerify('STD-####'),
            'nama_standar' => $this->faker->sentence(3),
            'deskripsi' => $this->faker->optional()->paragraph,
            'is_active' => true,
        ];
    }
}
