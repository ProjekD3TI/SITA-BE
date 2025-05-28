<?php

namespace Database\Factories;

use App\Models\NilaiBimbingan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NilaiBimbingan>
 */
class NilaiBimbinganFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = NilaiBimbingan::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $disiplin = $this->faker->randomFloat(1, 70, 100);
        $komunikasi = $this->faker->randomFloat(1, 70, 100);
        $skillPengetahuan = $this->faker->randomFloat(1, 70, 100);
        $tingkahLaku = $this->faker->randomFloat(1, 70, 100);

        // Menghitung rata-rata
        $rerataBimbingan = round(($disiplin + $komunikasi + $skillPengetahuan + $tingkahLaku) / 4, 1);

        return [
            'rerata_bimbingan' => $rerataBimbingan,
            'disiplin' => $disiplin,
            'komunikasi' => $komunikasi,
            'skill_pengetahuan' => $skillPengetahuan,
            'tingkah_laku' => $tingkahLaku,
        ];
    }

    /**
     * Menghasilkan nilai bimbingan yang sangat baik (>90).
     */
    public function excellent(): Factory
    {
        return $this->state(function () {
            $disiplin = $this->faker->randomFloat(1, 90, 100);
            $komunikasi = $this->faker->randomFloat(1, 90, 100);
            $skillPengetahuan = $this->faker->randomFloat(1, 90, 100);
            $tingkahLaku = $this->faker->randomFloat(1, 90, 100);

            $rerataBimbingan = round(($disiplin + $komunikasi + $skillPengetahuan + $tingkahLaku) / 4, 1);

            return [
                'rerata_bimbingan' => $rerataBimbingan,
                'disiplin' => $disiplin,
                'komunikasi' => $komunikasi,
                'skill_pengetahuan' => $skillPengetahuan,
                'tingkah_laku' => $tingkahLaku,
            ];
        });
    }
}
