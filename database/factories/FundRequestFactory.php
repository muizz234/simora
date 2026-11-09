<?php

namespace Database\Factories;

use App\Models\FundRequest;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundRequest>
 */
class FundRequestFactory extends Factory
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
            'decided_by' => null,
            'title' => fake()->randomElement(['Pengajuan Dana Kegiatan', 'Pengajuan Dana Konsumsi', 'Pengajuan Dana Transportasi']), 
            'description' => fake()->optional()->sentence(),
            'amount' => fake()->randomFloat(2, 100000, 3000000),
            'status' => fake()->randomElement(['menunggu', 'disetujui', 'ditolak']),
            'applicant' => fake()->optional()->name(),
            'decided_at' => null,
        ];
    }
}
