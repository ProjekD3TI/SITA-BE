<?php

namespace Database\Factories;

use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function definition(): array
    {
        $titles = ['Dr.', 'Prof.', 'Ir.', 'Dr. Ir.', ''];
        $degrees = ['M.Kom', 'M.Sc', 'M.T', 'M.Eng', 'Ph.D', 'M.Si', 'M.Cs'];
        
        $title = $titles[array_rand($titles)];
        $name = $this->faker->name();
        $degree = $degrees[array_rand($degrees)];
        
        // Format: either "Dr. Name, M.Kom" or just "Name, M.Kom" depending on title
        $fullName = trim("$title $name, $degree");
        
        // NIK format: year + month + counter
        $year = rand(1970, 1990);
        $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $counter = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $nik = substr($year, -2) . $month . $counter;
        
        return [
            'name' => $fullName,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'nik' => $nik,
            'status' => 1,
            'no_telp' => $this->faker->phoneNumber(),
        ];
    }
}