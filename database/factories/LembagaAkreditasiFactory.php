<?php

namespace Database\Factories;

use App\Models\LembagaAkreditasi;
use Illuminate\Database\Eloquent\Factories\Factory;

class LembagaAkreditasiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LembagaAkreditasi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'nama_lembaga' => $this->faker->company,
            'singkatan' => $this->faker->bothify('????'),
            'deskripsi' => $this->faker->optional()->sentence,
            'is_active' => $this->faker->boolean,
        ];
    }
}