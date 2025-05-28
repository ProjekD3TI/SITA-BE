<?php

namespace Database\Factories;

use App\Models\Ruang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ruang>
 */
class RuangFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = Ruang::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $buildings = ['Gedung A', 'Gedung B', 'Gedung C', 'Gedung D'];
        $floors = [1, 2, 3, 4];
        $rooms = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $building = $this->faker->randomElement($buildings);
        $floor = $this->faker->randomElement($floors);
        $room = $this->faker->randomElement($rooms);

        return [
            'nama_ruang' => "$building Lantai $floor Ruang $room",
        ];
    }
}
