<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

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
            'role' => 'admin_ta',
        ];
    }

    public function superadmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'superadmin',
            ];
        });
    }

    public function adminPengelola()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'admin_pengelola',
            ];
        });
    }
}