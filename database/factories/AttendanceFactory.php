<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
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
            'checked_at' => now(),
            'method' => fake()->randomElement(['qr', 'manual']),
        ];
    }
}
