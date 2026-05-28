<?php

namespace Database\Factories;

use App\Models\Edps;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\StandarMutu;
use Illuminate\Database\Eloquent\Factories\Factory;

class EdpsFactory extends Factory
{
    protected $model = Edps::class;

    public function definition(): array
    {
        return [
            'prodi_id' => Prodi::factory(),
            'periode_id' => PeriodeAkademik::factory(),
            'standar_mutu_id' => StandarMutu::factory(),
            'target' => $this->faker->numberBetween(50, 100),
            'capaian' => $this->faker->optional()->numberBetween(0, 100),
            'analisis' => $this->faker->optional()->paragraph,
            'status' => 'draft',
        ];
    }
}
