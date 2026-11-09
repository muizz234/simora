<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
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
            'created_by' => null,
            'name' => fake()->randomElement(['Rapat Internal', 'Pelatihan', 'Seminar', 'Workshop', 'Kunjungan Industri']), 
            'description' => fake()->optional()->sentence(),
            'location' => fake()->optional()->city(),
            'start_at' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'end_at' => fake()->dateTimeBetween('now', '+2 months'),
            'status' => fake()->randomElement(['perencanaan', 'pelaksanaan', 'evaluasi', 'pelaporan', 'selesai']),
        ];
    }
}
