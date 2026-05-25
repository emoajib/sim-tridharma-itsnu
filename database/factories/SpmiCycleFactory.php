<?php

namespace Database\Factories;

use App\Models\SpmiCycle;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpmiCycleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpmiCycle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'prodi_id' => \App\Models\Prodi::factory(),
            'periode_id' => \App\Models\PeriodeAkademik::factory(),
            'instrumen_id' => \App\Models\InstrumenAkreditasi::factory(),
            'tahap' => $this->faker->randomElement(['penetapan', 'pelaksanaan', 'evaluasi', 'pengendalian', 'peningkatan']),
            'kategori' => $this->faker->randomElement(['Akademik', 'Non-Akademik']),
            'nama_siklus' => $this->faker->sentence,
            'tanggal_mulai' => $this->faker->date,
            'tanggal_selesai' => $this->faker->optional()->date,
            'persentase_selesai' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->randomElement(['planned', 'in_progress', 'completed', 'cancelled']),
            'catatan' => $this->faker->optional()->sentence,
        ];
    }
}