<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Recruitment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recruitment>
 */
class RecruitmentFactory extends Factory
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
            'created_by' => null,
            'title' => fake()->randomElement(['Open Recruitment Staf Divisi', 'Rekrutmen Panitia Kegiatan', 'Calon Pengurus Divisi']), 
            'division' => fake()->optional()->word(),
            'description' => fake()->paragraph(),
            'deadline' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => 'dibuka',
        ];
    }
}
