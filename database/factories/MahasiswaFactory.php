<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nim' => fake()->unique()->numerify('##########'),
            'name' => fake()->name(),
            'faculty' => 'Teknik',
            'study_program' => fake()->randomElement(['Teknologi Informasi', 'Teknik Informatika', 'Teknik Elektro']),
            'prodi_id' => fake()->uuid(),
            'fakultas_id' => '2',
            'gender' => fake()->randomElement(['L', 'P']),
            'enrollment_year' => (string) fake()->year(),
            'synced_at' => now(),
        ];
    }
}
