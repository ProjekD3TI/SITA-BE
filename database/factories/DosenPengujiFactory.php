<?php

namespace Database\Factories;

use App\Models\DosenPenguji;
use App\Models\Dosen;
use App\Models\TugasAkhir;
use App\Models\NilaiUjianTA;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DosenPenguji>
 */
class DosenPengujiFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = DosenPenguji::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dosen_id' => Dosen::factory(),
            'tugas_akhir_id' => TugasAkhir::factory(),
            'nilai_ujian_ta_id' => null,
            'revisi' => $this->faker->paragraph(),
            'catatan_semhas' => $this->faker->paragraph(),
        ];
    }

    /**
     * Mengatur DosenPenguji dengan relasi yang sudah ada.
     */
    public function withExistingRelations(): Factory
    {
        return $this->state(function () {
            return [
                'dosen_id' => Dosen::inRandomOrder()->first()?->id ?? Dosen::factory()->create()->id,
                'tugas_akhir_id' => TugasAkhir::inRandomOrder()->first()?->id ?? TugasAkhir::factory()->create()->id,
            ];
        });
    }

    /**
     * Menyertakan NilaiUjianTA untuk DosenPenguji.
     */
    public function withNilaiUjianTA(): Factory
    {
        return $this->state(function () {
            return [
                'nilai_ujian_ta_id' => NilaiUjianTA::factory(),
            ];
        });
    }

    /**
     * Menetapkan NilaiUjianTA yang sudah ada untuk DosenPenguji.
     */
    public function withExistingNilaiUjianTA(): Factory
    {
        return $this->state(function () {
            return [
                'nilai_ujian_ta_id' => NilaiUjianTA::inRandomOrder()->first()?->id ?? NilaiUjianTA::factory()->create()->id,
            ];
        });
    }

    /**
     * Menetapkan tidak ada revisi yang diperlukan.
     */
    public function noRevisions(): Factory
    {
        return $this->state(function () {
            return [
                'revisi' => 'Tidak ada revisi yang diperlukan.',
            ];
        });
    }
}
