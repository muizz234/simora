<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
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
            'title' => fake()->randomElement(['Rapat Internal', 'Pengumuman Kegiatan', 'Libur Kepengurusan', 'Open Recruitment']),
            'content' => fake()->paragraph(),
            'published_at' => now(),
        ];
    }
}
