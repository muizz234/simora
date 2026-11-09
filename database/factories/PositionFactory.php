<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
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
            'member_id' => null,
            'name' => fake()->randomElement(['Ketua Umum', 'Wakil Ketua Umum', 'Sekretaris Umum', 'Bendahara Umum', 'Koordinator Divisi', 'Staf Divisi']),
            'description' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
