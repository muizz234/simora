<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Evaluation;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'activity_id' => Activity::factory(),
            'created_by' => null,
            'title' => fake()->randomElement(['Evaluasi Kegiatan', 'LPJ Kegiatan', 'Refleksi Program']), 
            'content' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(['evaluasi', 'lpj']),
            'status' => fake()->randomElement(['draft', 'selesai', 'disetujui']),
        ];
    }
}
