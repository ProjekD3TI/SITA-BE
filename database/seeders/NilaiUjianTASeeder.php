<?php

namespace Database\Seeders;

use App\Models\NilaiUjianTA;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NilaiUjianTASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NilaiUjianTA::create([
            'kualitas_tata_laporan' => 85,
            'kemampuan_teknis' => 90,
            'kualitas_produk' => 88,
            'presentasi' => 92,
            'rerata_ujian_ta' => 80,
        ]);

        NilaiUjianTA::create([
            'kualitas_tata_laporan' => 75,
            'kemampuan_teknis' => 80,
            'kualitas_produk' => 78,
            'presentasi' => 82,
            'rerata_ujian_ta' => 76,
        ]);

        NilaiUjianTA::create([
            'kualitas_tata_laporan' => 90,
            'kemampuan_teknis' => 95,
            'kualitas_produk' => 93,
            'presentasi' => 97,
            'rerata_ujian_ta' => 92,
        ]);
    }
}
