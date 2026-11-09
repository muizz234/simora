<?php

namespace Database\Factories;

use App\Models\CashTransaction;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashTransaction>
 */
class CashTransactionFactory extends Factory
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
            'type' => fake()->randomElement(['pemasukan', 'pengeluaran']),
            'title' => fake()->randomElement(['Iuran Anggota', 'Sponsorship', 'Belanja Konsumsi', 'Sewa Tempat', 'Transportasi']), 
            'amount' => fake()->randomFloat(2, 50000, 5000000),
            'date' => fake()->date(),
            'category' => fake()->optional()->word(),
        ];
    }
}
