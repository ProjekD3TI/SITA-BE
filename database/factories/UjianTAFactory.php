<?php

namespace Database\Factories;

use App\Models\UjianTA;
use App\Models\TugasAkhir;
use App\Models\Ruang;
use App\Models\Sesi;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UjianTA>
 */
class UjianTAFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = UjianTA::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tugas_akhir_id' => TugasAkhir::factory(),
            'tanggal_ujian' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'ruang_id' => Ruang::factory(),
            'sesi_id' => Sesi::factory(),
            'semester_id' => Semester::factory(),
            'tahun_ajaran_id' => TahunAjaran::factory(),
            'status_ujian' => $this->faker->randomElement(['aktif', 'non-aktif']), // Nilai yang valid untuk enum
        ];
    }
    /**
     * Mengatur UjianTA dengan relasi yang sudah ada.
     */
    public function withExistingRelations(): Factory
    {
        return $this->state(function () {
            return [
                'tugas_akhir_id' => TugasAkhir::inRandomOrder()->first()?->id ?? TugasAkhir::factory()->create()->id,
                'ruang_id' => Ruang::inRandomOrder()->first()?->id ?? Ruang::factory()->create()->id,
                'sesi_id' => Sesi::inRandomOrder()->first()?->id ?? Sesi::factory()->create()->id,
                'semester_id' => Semester::inRandomOrder()->first()?->id ?? Semester::factory()->create()->id,
                'tahun_ajaran_id' => TahunAjaran::inRandomOrder()->first()?->id ?? TahunAjaran::factory()->create()->id,
            ];
        });
    }
}
