<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Berkas;

class BerkasFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Berkas::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'berkasable_type' => $this->faker->word,
            'berkasable_id' => $this->faker->randomDigitNotNull,
            'nama_berkas' => $this->faker->sentence(3),
            'file_path' => 'uploads/' . $this->faker->uuid . '.pdf',
            'status_valid' => $this->faker->randomElement([0, 1]),
        ];
    }

    /**
     * Indicate that berkasable is related to a specific model (e.g., TugasAkhir or User).
     *
     * @param string $type
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forBerkasable(string $type, int $id): Factory
    {
        return $this->state([
            'berkasable_type' => $type,
            'berkasable_id' => $id,
        ]);
    }
}
