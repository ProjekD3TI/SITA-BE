<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition() {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'role' => 'admin_ta',
        ];
    }

    public function superadmin() {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'superadmin',
            ];
        });
    }

    public function adminPengelola() {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'admin_pengelola',
            ];
        });
    }
}