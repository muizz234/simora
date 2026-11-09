<?php

namespace Database\Factories;

use App\Models\Dues;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dues>
 */
class DuesFactory extends Factory
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
            'user_id' => null,
            'name' => fake()->name(),
            'amount' => fake()->randomElement([25000, 50000, 100000]),
            'status' => fake()->randomElement(['lunas', 'belum_bayar']),
            'paid_at' => null,
            'period' => (string) now()->format('Y'),
        ];
    }
}
