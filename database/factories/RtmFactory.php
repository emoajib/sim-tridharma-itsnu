<?php

namespace Database\Factories;

use App\Models\Rtm;
use Illuminate\Database\Eloquent\Factories\Factory;

class RtmFactory extends Factory
{
    protected $model = Rtm::class;

    public function definition(): array
    {
        return [
            'judul' => $this->faker->sentence(4),
            'tanggal_rapat' => $this->faker->date(),
            'agenda' => $this->faker->optional()->paragraph,
        ];
    }
}
