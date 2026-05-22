<?php

namespace Database\Factories;

use App\Models\PeriodeAkademik;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodeAkademikFactory extends Factory
{
    protected $model = PeriodeAkademik::class;

    public function definition(): array
    {
        return [
            'kode_periode' => fake()->unique()->numerify('####.#'),
            'nama_periode' => fake()->year().'/'.fake()->year().' '.fake()->randomElement(['Ganjil', 'Genap']),
        ];
    }
}
