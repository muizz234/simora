<?php

namespace Database\Factories;

use App\Models\Archive;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Archive>
 */
class ArchiveFactory extends Factory
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
            'uploaded_by' => null,
            'title' => fake()->randomElement(['SK Kepengurusan', 'Surat Tugas Kegiatan', 'Surat Undangan', 'SK Panitia']), 
            'type' => fake()->randomElement(['sk', 'surat_masuk', 'surat_keluar']),
            'number' => fake()->optional()->bothify('###/SK/?#/FT/?##'),
            'date' => fake()->date(),
            'file_path' => null,
        ];
    }
}
