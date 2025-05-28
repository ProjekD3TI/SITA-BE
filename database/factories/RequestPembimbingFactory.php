<?php

namespace Database\Factories;

use App\Models\RequestPembimbing;
use App\Models\TugasAkhir;
use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestPembimbing>
 */
class RequestPembimbingFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = RequestPembimbing::class;

    public function definition()
    {
        return [
            'tugas_akhir_id' => \App\Models\TugasAkhir::factory(),
            'dosen_id' => \App\Models\Dosen::factory(),
            'status_request' => $this->faker->randomElement([0, 1, 2]), // Gunakan integer, bukan string
            'tanggal_request' => $this->faker->date(),
            'tanggal_respon' => $this->faker->date(),
        ];
    }

    // Tambahkan state methods untuk kemudahan penggunaan
    public function diterima()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_request' => 1, // Kode untuk 'Diterima'
            ];
        });
    }

    public function ditolak()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_request' => 2, // Kode untuk 'Ditolak'
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_request' => 0, // Kode untuk 'Pending'
            ];
        });
    }
}
