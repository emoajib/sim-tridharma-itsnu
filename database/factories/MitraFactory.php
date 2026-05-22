<?php

namespace Database\Factories;

use App\Models\Mitra;
use Illuminate\Database\Eloquent\Factories\Factory;

class MitraFactory extends Factory
{
    protected $model = Mitra::class;

    public function definition(): array
    {
        return [
            'nama_mitra' => fake()->company(),
            'jenis_mitra' => fake()->randomElement(['Industri', 'Pendidikan', 'Pemerintah']),
            'alamat' => fake()->address(),
            'kontak' => fake()->name(),
            'telepon' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
        ];
    }
}
