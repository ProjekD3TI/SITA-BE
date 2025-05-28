<?php

namespace Database\Factories;

use App\Models\JabatanDosen;
use App\Models\Jabatan;
use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JabatanDosen>
 */
class JabatanDosenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JabatanDosen::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dosen_id' => Dosen::factory(),
            'jabatan_id' => Jabatan::factory(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Configure the JabatanDosen with existing relationships.
     */
    public function withExistingRelations(): Factory
    {
        return $this->state(function () {
            return [
                'dosen_id' => Dosen::inRandomOrder()->first()?->id ?? Dosen::factory()->create()->id,
                'jabatan_id' => Jabatan::inRandomOrder()->first()?->id ?? Jabatan::factory()->create()->id,
            ];
        });
    }

    /**
     * Indicate that the JabatanDosen is active.
     */
    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the JabatanDosen is inactive.
     */
    public function inactive(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
