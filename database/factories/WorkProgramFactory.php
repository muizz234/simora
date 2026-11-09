<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\WorkProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkProgram>
 */
class WorkProgramFactory extends Factory
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
            'name' => fake()->randomElement(['Program Kerja Tahunan', 'Rapat Kerja', 'Pelatihan Kepemimpinan', 'Bakti Sosial', 'Seminar Teknologi']), 
            'description' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['perencanaan', 'pelaksanaan', 'evaluasi', 'pelaporan', 'selesai']),
            'start_date' => fake()->date(),
            'end_date' => fake()->dateTimeBetween('now', '+3 months'),
        ];
    }
}
