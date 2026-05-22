<?php

namespace Database\Factories;

use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdiFactory extends Factory
{
    protected $model = Prodi::class;

    public function definition(): array
    {
        return [
            'kode_prodi' => fake()->unique()->numerify('#######'),
            'nama_prodi' => 'Program Studi '.fake()->word(),
            'fakultas_id' => Fakultas::factory(),
            'jenjang' => fake()->randomElement(['S1', 'S2', 'D3']),
        ];
    }
}
