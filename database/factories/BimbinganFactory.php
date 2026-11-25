<?php

namespace Database\Factories;

use App\Models\Bimbingan;
use App\Models\TugasAkhir;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bimbingan>
 */
class BimbinganFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = Bimbingan::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $babs = [1, 2, 3, 4, 5]; // Nilai numerik sesuai dengan definisi tabel
        $statuses = [0, 1, 2]; // 0: Belum diverifikasi, 1: Diverifikasi, 2: Ditolak

        return [
            'tugas_akhir_id' => TugasAkhir::factory(),
            'catatan' => $this->faker->paragraph(),
            'bab' => $this->faker->randomElement($babs),
            'status_verifikasi' => $this->faker->randomElement($statuses),
            'tanggal_bimbingan' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
        ];
    }

    /**
         /**
     * Mengatur Bimbingan dengan TugasAkhir yang sudah ada.
     */
    public function forExistingTugasAkhir(): Factory
    {
        return $this->state(function () {
            return [
                'tugas_akhir_id' => TugasAkhir::inRandomOrder()->first()?->id ?? TugasAkhir::factory()->create()->id,
            ];
        });
    }

    /**
     * Menandakan bahwa bimbingan telah diverifikasi.
     */
    public function verified(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status_verifikasi' => 'Terverifikasi',
            ];
        });
    }
}
