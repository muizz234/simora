<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
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
            'work_program_id' => null,
            'program_name' => fake()->randomElement(['Program Kerja Tahunan', 'Rapat Kerja', 'Pelatihan Kepemimpinan', 'Bakti Sosial', 'Seminar Teknologi']),
            'year' => (int) now()->format('Y'),
            'allocated' => fake()->randomFloat(2, 500000, 10000000),
            'realized' => 0,
        ];
    }
}
