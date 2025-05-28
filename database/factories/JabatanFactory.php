<?php

namespace Database\Factories;

use App\Models\Jabatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jabatan>
 */
class JabatanFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = Jabatan::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jabatanList = [
            'Ketua Program Studi' => 'Memimpin program studi',
            'Sekretaris Program Studi' => 'Mengelola administrasi program studi',
            'Koordinator Tugas Akhir' => 'Mengkoordinasi pelaksanaan tugas akhir',
            'Dosen Tetap' => 'Melaksanakan tugas mengajar dan membimbing',
            'Dosen Tidak Tetap' => 'Melaksanakan tugas mengajar paruh waktu',
        ];

        $name = $this->faker->unique()->randomElement(array_keys($jabatanList));

        return [
            'name' => $name,
            'description' => $jabatanList[$name],
        ];
    }
}
