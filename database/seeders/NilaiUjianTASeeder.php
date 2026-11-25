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

        // Raffael
        NilaiUjianTA::create([
            'ujian_ta_id' => 1,
            'dosen_id' => 9,
            'kualitas_tata_laporan' => 85,
            'kemampuan_teknis' => 90,
            'kualitas_produk' => 88,
            'presentasi' => 92,
            'rerata_ujian_ta' => 80,
        ]);
        NilaiUjianTA::create([
            'ujian_ta_id' => 1,
            'dosen_id' => 15,
            'kualitas_tata_laporan' => 75,
            'kemampuan_teknis' => 80,
            'kualitas_produk' => 78,
            'presentasi' => 82,
            'rerata_ujian_ta' => 76,
        ]);
        NilaiUjianTA::create([
            'ujian_ta_id' => 1,
            'dosen_id' => 4,
            'kualitas_tata_laporan' => 90,
            'kemampuan_teknis' => 95,
            'kualitas_produk' => 93,
            'presentasi' => 97,
            'rerata_ujian_ta' => 92,
        ]);
    }
}
