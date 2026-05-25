<?php

namespace Database\Factories;

use App\Models\AuditMutu;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditMutuFactory extends Factory
{
    protected $model = AuditMutu::class;

    public function definition(): array
    {
        return [
            'prodi_id' => Prodi::factory(),
            'periode_id' => PeriodeAkademik::factory(),
            'judul_audit' => 'Audit ' . fake()->sentence(3),
            'temuan' => fake()->paragraph(),
            'tanggal_audit' => fake()->date(),
            'status' => fake()->randomElement(['draft', 'submitted', 'in_progress', 'awaiting_verification', 'verified', 'closed']),
            'severity' => fake()->randomElement(['ringan', 'sedang', 'berat', 'kritis']),
        ];
    }
}
