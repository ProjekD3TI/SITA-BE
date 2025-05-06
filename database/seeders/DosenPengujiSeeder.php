<?php

namespace Database\Seeders;

use App\Models\DosenPenguji;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DosenPengujiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DosenPenguji::create([
            'dosen_id' => 2,
            'tugas_akhir_id' => 1,
            'nilai_ujian_ta_id' => 1,
            'revisi' => 'Perbaiki Bab 1',
            'catatan_semhas' => 'Perbaiki Bab 1',
        ]);
        DosenPenguji::create([
            'dosen_id' => 3,
            'tugas_akhir_id' => 1,
            'nilai_ujian_ta_id' => 2,
            'revisi' => 'Perbaiki Bab 2',
            'catatan_semhas' => 'Perbaiki Bab 2',
        ]);
        DosenPenguji::create([
            'dosen_id' => 1,
            'tugas_akhir_id' => 1,
            'nilai_ujian_ta_id' => 3,
            'revisi' => 'Perbaiki Bab 3',
            'catatan_semhas' => 'Perbaiki Bab 2',
        ]);
    }
}
