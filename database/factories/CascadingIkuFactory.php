<?php

namespace Database\Factories;

use App\Models\CascadingIku;
use App\Models\IndikatorIku;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

class CascadingIkuFactory extends Factory
{
    protected $model = CascadingIku::class;

    public function definition(): array
    {
        return [
            'iku_id' => IndikatorIku::factory(),
            'periode_id' => PeriodeAkademik::factory(),
            'unit_type' => 'Prodi',
            'unit_id' => Prodi::factory(),
            'target' => $this->faker->randomFloat(2, 0, 100),
            'capaian' => 0,
            'catatan' => null,
        ];
    }
}
