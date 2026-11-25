<?php

namespace Database\Factories;

use App\Models\Sesi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sesi>
 */
class SesiFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = Sesi::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Menghasilkan waktu mulai antara jam 8 pagi dan 3 sore
        $waktuMulai = $this->faker->dateTimeBetween('08:00', '15:00')->format('H:i:s');

        // Membuat waktu selesai 2 jam setelah waktu mulai
        $waktuSelesai = date('H:i:s', strtotime($waktuMulai . ' +2 hours'));

        return [
            'nama_sesi' => 'Sesi ' . $this->faker->numberBetween(1, 5),
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ];
    }
}
