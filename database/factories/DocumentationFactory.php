<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Documentation;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Documentation>
 */
class DocumentationFactory extends Factory
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
            'uploaded_by' => null,
            'title' => fake()->randomElement(['Foto Kegiatan', 'Dokumen Acara', 'Rekaman Opening']), 
            'type' => fake()->randomElement(['foto', 'dokumen']),
            'file_path' => null,
        ];
    }
}
