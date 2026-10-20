<?php

namespace Database\Factories;

use App\Models\CoralColony;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoralColony>
 */
class CoralColonyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CoralColony::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $species = [
            'Acropora cervicornis',
            'Acropora palmata',
            'Porites lutea',
            'Montipora capitata',
            'Pocillopora damicornis',
        ];

        $zones = [
            'Sektor Utara - Karimunjawa',
            'Sektor Selatan - Karimunjawa',
            'Zona Inti Terumbu - Bunaken',
            'Zona Restorasi Barat - Raja Ampat',
        ];

        $statuses = [
            'Sehat',
            'Pemutihan/Bleaching',
            'Pemulihan',
        ];

        return [
            'kode_fragmen' => 'CRL-' . fake()->unique()->numberBetween(1000, 9999),
            'spesies_ilmiah' => fake()->randomElement($species),
            'zona_terumbu' => fake()->randomElement($zones),
            'kedalaman_meter' => fake()->randomFloat(2, 2.0, 30.0),
            'persentase_hidup' => fake()->numberBetween(10, 100),
            'status_kesehatan' => fake()->randomElement($statuses),
            'catatan_observasi' => fake()->optional()->sentence(),
        ];
    }
}
