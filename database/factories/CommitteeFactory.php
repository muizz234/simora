<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Committee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Committee>
 */
class CommitteeFactory extends Factory
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
            'user_id' => null,
            'name' => fake()->name(),
            'role' => fake()->randomElement(['ketua', 'sekretaris', 'bendahara', 'humas', 'perkap', 'anggota']),
        ];
    }
}
