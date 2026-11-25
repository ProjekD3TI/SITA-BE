<?php

namespace Database\Factories;

use App\Models\NilaiUjianTA;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NilaiUjianTA>
 */
class NilaiUjianTAFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = NilaiUjianTA::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */    public function definition(): array
    {
        $kualitasTataLaporan = $this->faker->randomFloat(1, 70, 100);
        $kemampuanTeknis = $this->faker->randomFloat(1, 70, 100);
        $kualitasProduk = $this->faker->randomFloat(1, 70, 100);
        $presentasi = $this->faker->randomFloat(1, 70, 100);

        // Menghitung rata-rata
        $rerataUjianTA = round(($kualitasTataLaporan + $kemampuanTeknis + $kualitasProduk + $presentasi) / 4, 1);

        return [
            'kualitas_tata_laporan' => $kualitasTataLaporan,
            'kemampuan_teknis' => $kemampuanTeknis,
            'kualitas_produk' => $kualitasProduk,
            'presentasi' => $presentasi,
            'rerata_ujian_ta' => $rerataUjianTA,
        ];
    }

    /**
     * Menghasilkan nilai ujian yang sangat baik (>90).
     */
    public function excellent(): Factory
    {
        return $this->state(function () {
            $kualitasTataLaporan = $this->faker->randomFloat(1, 90, 100);
            $kemampuanTeknis = $this->faker->randomFloat(1, 90, 100);
            $kualitasProduk = $this->faker->randomFloat(1, 90, 100);
            $presentasi = $this->faker->randomFloat(1, 90, 100);

            $rerataUjianTA = round(($kualitasTataLaporan + $kemampuanTeknis + $kualitasProduk + $presentasi) / 4, 1);

            return [
                'kualitas_tata_laporan' => $kualitasTataLaporan,
                'kemampuan_teknis' => $kemampuanTeknis,
                'kualitas_produk' => $kualitasProduk,
                'presentasi' => $presentasi,
                'rerata_ujian_ta' => $rerataUjianTA,
            ];
        });
    }
}
