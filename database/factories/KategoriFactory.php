<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kategori>
 */
class KategoriFactory extends Factory
{
    /**
     * Model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = Kategori::class;

    /**
     * Mendefinisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Web Development',
            'Mobile Application',
            'Game Development',
            'Artificial Intelligence',
            'Machine Learning',
            'Data Science',
            'IoT (Internet of Things)',
            'Cybersecurity',
            'Augmented Reality',
            'Virtual Reality',
            'Blockchain',
            'Cloud Computing',
        ];

        return [
            'nama_kategori' => $this->faker->unique()->randomElement($categories),
        ];
    }
}
