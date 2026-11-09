<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'full_name' => fake()->company(),
            'banner_color' => '#171717',
            'accent_color' => '#3687ff',
            'focus' => fake()->word(),
            'period' => null,
            'registration_url' => null,
            'instagram_url' => null,
            'tiktok_url' => null,
            'youtube_url' => null,
            'whatsapp_url' => null,
            'facebook_url' => null,
            'x_url' => null,
            'email' => null,
            'telegram_url' => null,
            'description' => fake()->sentence(),
            'vision' => null,
            'mission' => null,
            'logo_path' => null,
            'sort_order' => 0,
        ];
    }
}
