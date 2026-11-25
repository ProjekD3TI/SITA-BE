<?php

namespace Database\Factories;

use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proposal>
 */
class ProposalFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = Proposal::class;

    public function definition()
    {
        return [
            'judul_proposal' => $this->faker->sentence(),
            // Ubah dari string 'Disetujui' menjadi integer
            'status_proposal' => $this->faker->randomElement([0, 1, 2]),
            'tanggal_unggah' => $this->faker->date(),
        ];
    }

    // Tambahkan state methods untuk kemudahan penggunaan
    public function disetujui()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_proposal' => 1, // Kode numerik untuk 'Disetujui'
            ];
        });
    }

    public function ditolak()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_proposal' => 2, // Kode numerik untuk 'Ditolak'
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_proposal' => 0, // Kode numerik untuk 'Pending'
            ];
        });
    }
}
