<?php

namespace Database\Factories;

use App\Models\InstrumenAkreditasi;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstrumenAkreditasiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InstrumenAkreditasi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'lembaga_id' => \App\Models\LembagaAkreditasi::factory(),
            'nama_instrumen' => $this->faker->sentence,
            'matriks_kriteria' => json_encode([
                'VISI' => 15,
                'TATA' => 10,
                'MHS' => 10,
                'SDM' => 15,
                'KUR' => 15,
                'PEM' => 10,
                'PEN' => 10,
                'LUL' => 10,
                'MUTU' => 5,
            ]),
        ];
    }
}