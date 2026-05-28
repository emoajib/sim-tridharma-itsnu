<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndikatorIkuFactory extends Factory
{
    protected $model = \App\Models\IndikatorIku::class;

    public function definition(): array
    {
        return [
            'kode_iku' => $this->faker->unique()->regexify('IKU-[A-Z0-9]{3}'),
            'nama_indikator' => $this->faker->sentence(3),
            'deskripsi' => $this->faker->paragraph(),
            'satuan' => 'persentase',
            'bobot' => $this->faker->randomFloat(2, 0, 100),
            'target' => $this->faker->randomFloat(2, 0, 100),
            'is_active' => true,
        ];
    }
}
