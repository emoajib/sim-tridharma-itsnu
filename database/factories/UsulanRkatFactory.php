<?php

namespace Database\Factories;

use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\UsulanRkat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsulanRkatFactory extends Factory
{
    protected $model = UsulanRkat::class;

    public function definition(): array
    {
        return [
            'prodi_id' => Prodi::factory(),
            'periode_id' => PeriodeAkademik::factory(),
            'judul_kegiatan' => $this->faker->sentence(4),
            'deskripsi_kegiatan' => $this->faker->paragraph(),
            'estimasi_biaya' => $this->faker->randomFloat(2, 1000000, 100000000),
            'status' => 'submitted',
            'user_id' => User::factory(),
        ];
    }
}
