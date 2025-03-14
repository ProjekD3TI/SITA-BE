<?php

namespace Database\Seeders;

use App\Models\Ruang;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RuangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ruang_data = [
            ['nama_ruang' => 'Ruang Tefa'],
            ['nama_ruang' => 'Ruang RPL'],
            ['nama_ruang' => 'Ruang Pemrograman'],
            ['nama_ruang' => 'Ruang MR'],
            ['nama_ruang' => 'Ruang Jaringan'],
        ];

        foreach ($ruang_data as $ruang) {
            Ruang::create($ruang);
        }
    }
}
