<?php

namespace Database\Seeders;

use App\Models\TahunAjaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        TahunAjaran::create([
            'tahun_ajaran' => '2021/2022',
            'is_default' => 0,
        ]);
        TahunAjaran::create([
            'tahun_ajaran' => '2022/2023',
            'is_default' => 0,
        ]);
        TahunAjaran::create([
            'tahun_ajaran' => '2023/2024',
            'is_default' => 0,
        ]);
        TahunAjaran::create([
            'tahun_ajaran' => '2024/2025',
            'is_default' => 1,
        ]);
    }
}
