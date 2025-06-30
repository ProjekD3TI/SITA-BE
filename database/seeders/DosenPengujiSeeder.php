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
        // Raffael
        DosenPenguji::create([
            'dosen_id' => 9,
            'tugas_akhir_id' => 1,
            // 'nilai_ujian_ta_id' => 1,
            'jabatan_penguji' => 'ketua',
            // 'revisi' => 'Perbaiki Bab 1',
            // 'catatan_semhas' => 'Perbaiki Bab 1',
        ]);
        DosenPenguji::create([
            'dosen_id' => 15,
            'tugas_akhir_id' => 1,
            // 'nilai_ujian_ta_id' => 2,
            'jabatan_penguji' => 'sekretaris',
            // 'revisi' => 'Perbaiki Bab 2',
            // 'catatan_semhas' => 'Perbaiki Bab 2',
        ]);
        DosenPenguji::create([
            'dosen_id' => 4,
            'tugas_akhir_id' => 1,
            // 'nilai_ujian_ta_id' => 3,
            'jabatan_penguji' => 'penguji',
            // 'revisi' => 'Perbaiki Bab 3',
            // 'catatan_semhas' => 'Perbaiki Bab 2',
        ]);
    }
}
