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
                'name' => 'Raffael Aldrich Setiawa',
                'email' => 'raffael@example.com',
                'no_telp' => '081234567890',
                'password' => bcrypt('123123123'),
                'status' => 1,
            ],
            [
                'nim' => 'V3423071',
                'name' => 'Mahasiswa Dua',
                'email' => 'mahasiswa2@example.com',
                'no_telp' => '081234567891',
                'password' => bcrypt('123123123'),
                'status' => 1,
            ],
            [
                'nim' => 'V3423072',
                'name' => 'Mahasiswa Tiga',
                'email' => 'mahasiswa3@example.com',
                'no_telp' => '081234567892',
                'password' => bcrypt('123123123'),
                'status' => 1,
            ],
        ]);
    }
}
