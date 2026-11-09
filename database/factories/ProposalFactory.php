<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
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
            'activity_id' => null,
            'submitted_by' => null,
            'decided_by' => null,
            'title' => fake()->randomElement(['Proposal Kegiatan', 'Pengajuan Dana Kegiatan', 'Usulan Program Kerja']), 
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['menunggu', 'disetujui', 'ditolak']),
            'decided_at' => null,
        ];
    }
}
