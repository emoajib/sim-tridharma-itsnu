<?php

namespace Database\Factories;

use App\Models\AuditMutu;
use App\Models\Capa;
use Illuminate\Database\Eloquent\Factories\Factory;

class CapaFactory extends Factory
{
    protected $model = Capa::class;

    public function definition(): array
    {
        return [
            'audit_mutu_id' => AuditMutu::factory(),
            'root_cause_analysis' => fake()->paragraph(),
            'corrective_action' => fake()->paragraph(),
            'corrective_deadline' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'status' => 'open',
        ];
    }
}
