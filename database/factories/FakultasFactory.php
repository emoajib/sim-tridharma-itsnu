<?php

namespace Database\Factories;

use App\Models\Fakultas;
use Illuminate\Database\Eloquent\Factories\Factory;

class FakultasFactory extends Factory
{
    protected $model = Fakultas::class;

    public function definition(): array
    {
        return [
            'kode_fakultas' => strtoupper(fake()->bothify('??###')),
            'nama_fakultas' => fake()->company(),
        ];
    }
}
