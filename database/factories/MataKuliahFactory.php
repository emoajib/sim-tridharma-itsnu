<?php

namespace Database\Factories;

use App\Models\MataKuliah;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

class MataKuliahFactory extends Factory
{
    protected $model = MataKuliah::class;

    public function definition(): array
    {
        return [
            'kode_mk' => strtoupper(fake()->bothify('??###')),
            'nama_mk' => fake()->sentence(3),
            'prodi_id' => Prodi::factory(),
            'sks' => fake()->numberBetween(2, 4),
        ];
    }
}
