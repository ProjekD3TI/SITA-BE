<?php

namespace Database\Factories;

use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->faker->addProvider(new \Faker\Provider\id_ID\Person($this->faker));
    }

    public function definition()
    {
        return [
            'name' => $this->faker->name('id_ID'), // nama Indonesia
            'email' => $this->faker->userName() . '@staff.uns.ac.id',
            'password' => Hash::make('123123123'),
            'nik' => $this->faker->unique()->numerify('##########'),
            'status' => 1,
        ];
    }
}