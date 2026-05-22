<?php

namespace Database\Factories;

use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function definition(): array
    {
        return [
            'nidn' => fake()->unique()->numerify('##########'),
            'nama_depan' => fake()->firstName(),
            'nama_belakang' => fake()->lastName(),
            'prodi_id' => Prodi::factory(),
        ];
    }
}
