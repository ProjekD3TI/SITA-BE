<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mahasiswa')->insert([
            [
                'nim' => 'V3423070',
                'name' => 'Raffael Aldrich Setiawan',
                'email' => 'raffael@gmail.com',
                'no_telp' => '081234567890',
                'password' => bcrypt('123123123'),
                'status' => 1,
            ],
            [
                'nim' => 'V3423072',
                'name' => 'Raihan Kafi Hufayda',
                'email' => 'raihan@gmail.com',
                'no_telp' => '081234567890',
                'password' => bcrypt('123123123'),
                'status' => 1,
            ],
            [
                'nim' => 'V3423075',
                'name' => 'Rizqi Andri Wijaya',
                'email' => 'rizqi@gmail.com',
                'no_telp' => '081234567890',
                'password' => bcrypt('123123123'),
                'status' => 1,
            ],
            [
                'nim' => 'V3423077',
                'name' => 'Satria Kusuma Mahardika',
                'email' => 'dika@gmail.com',
                'no_telp' => '081234567890',
                'password' => bcrypt('123123123'),
                'status' => 1,
            ],
            [
                'nim' => 'V3423085',
                'name' => 'Veri Tri Ariyanto',
                'email' => 'veri@gmail.com',
                'no_telp' => '081234567890',
                'password' => bcrypt('123123123'),
                'status' => 1,
            ],
        ]);
    }
}
