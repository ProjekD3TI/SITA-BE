<?php

namespace Database\Factories;

use App\Models\TugasAkhir;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Proposal;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\NilaiBimbingan;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TugasAkhir>
 */
class TugasAkhirFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = TugasAkhir::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefixes = [
            'Pengembangan',
            'Implementasi',
            'Analisis',
            'Perancangan',
            'Evaluasi',
            'Studi Kasus',
            'Optimasi',
            'Aplikasi',
            'Sistem',
            'Platform'
        ];

        $topics = [
            'Sistem Informasi Akademik',
            'Aplikasi Mobile untuk Pendidikan',
            'E-Commerce',
            'Machine Learning untuk Prediksi',
            'Internet of Things',
            'Augmented Reality',
            'Virtual Reality',
            'Data Mining',
            'Big Data',
            'Blockchain',
            'Cloud Computing',
            'Cybersecurity',
            'Smart City',
            'Aplikasi Berbasis Web',
            'Sistem Pendukung Keputusan'
        ];

        $methods = [
            'Berbasis Web',
            'Menggunakan Flutter',
            'Dengan Metode Scrum',
            'Menggunakan Algoritma Genetika',
            'Berbasis Android',
            'Menggunakan Laravel',
            'Berbasis React Native',
            'Dengan Pendekatan SDLC',
            'Menggunakan Deep Learning',
            'Dengan Metode XP',
            'Berbasis Microservices',
            'Menggunakan Firebase'
        ];

        return [
            'mahasiswa_id' => User::factory(),
            'proposal_id' => Proposal::factory(),
            'semester_id' => Semester::factory(),
            'dosen_pembimbing_id' => Dosen::factory(),
            'tahun_ajaran_id' => TahunAjaran::factory(),
            'nilai_bimbingan_id' => null,
            'kategori_id' => Kategori::factory(),
            'judul_tugas_akhir' => $this->faker->randomElement($prefixes) . ' ' .
                $this->faker->randomElement($topics) . ' ' .
                $this->faker->randomElement($methods),
            'catatan_proposal' => $this->faker->paragraph(),
            'nilai_proposal' => $this->faker->randomFloat(2, 60, 100),
            'nilai_akhir' => null,
            'status_valid' => $this->faker->randomElement([0, 1]),
            'status_aktif' => $this->faker->randomElement([0, 1]),
        ];
    }

    /**
     * Configure the TugasAkhir with existing relationships.
         /**
     * Mengatur TugasAkhir dengan relasi yang sudah ada.
     */
    public function withExistingRelations(): Factory
    {
        return $this->state(function () {
            return [
                'mahasiswa_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
                'proposal_id' => Proposal::inRandomOrder()->first()?->id ?? Proposal::factory()->create()->id,
                'semester_id' => Semester::inRandomOrder()->first()?->id ?? Semester::factory()->create()->id,
                'dosen_pembimbing_id' => Dosen::inRandomOrder()->first()?->id ?? Dosen::factory()->create()->id,
                'tahun_ajaran_id' => TahunAjaran::inRandomOrder()->first()?->id ?? TahunAjaran::factory()->create()->id,
                'kategori_id' => Kategori::inRandomOrder()->first()?->id ?? Kategori::factory()->create()->id,
            ];
        });
    }

    /**
     * Menandakan bahwa TugasAkhir valid.
     */
    public function valid(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status_valid' => 1,
            ];
        });
    }

    /**
     * Menandakan bahwa TugasAkhir aktif.
     */
    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status_aktif' => 1,
            ];
        });
    }

    /**
     * Menetapkan NilaiBimbingan ke TugasAkhir.
     */
    public function withNilaiBimbingan(): Factory
    {
        return $this->state(function () {
            return [
                'nilai_bimbingan_id' => NilaiBimbingan::factory(),
            ];
        });
    }
}
