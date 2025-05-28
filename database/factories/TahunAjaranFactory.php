<?php

namespace Database\Factories;

use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TahunAjaran>
 */
class TahunAjaranFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = TahunAjaran::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = $this->faker->numberBetween(2020, 2025);
        $endYear = $startYear + 1;

        return [
            'tahun_ajaran' => $startYear . '/' . $endYear,
            'is_default' => false,
        ];
    }

    /**
     * Menandakan bahwa tahun ajaran ini adalah default.
     */
    public function asDefault(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true,
            ];
        });
    }

    /**
     * Menghasilkan tahun ajaran saat ini.
     */
    public function current(): Factory
    {
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        return $this->state(function (array $attributes) use ($currentYear, $nextYear) {
            return [
                'tahun_ajaran' => $currentYear . '/' . $nextYear,
            ];
        });
    }
}
