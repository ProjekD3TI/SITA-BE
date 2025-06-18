<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $nim = 'M' . rand(3118, 3123) . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return [
            'nim' => $nim,
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'no_telp' => '08' . rand(10, 99) . rand(1000, 9999) . rand(1000, 9999),
            'password' => Hash::make('password123'),
            'status' => 1,
        ];
    }
}