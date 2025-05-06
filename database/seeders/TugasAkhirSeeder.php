<?php

namespace Database\Seeders;

use App\Models\TugasAkhir;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TugasAkhirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TugasAkhir::create([
            'mahasiswa_id' => 1,
            'dosen_pembimbing_id' => 1,
            'judul_tugas_akhir' => 'Sistem Informasi Akademik',
            'status_aktif' => 1,
        ]);

        TugasAkhir::create([
            'mahasiswa_id' => 2,
            'dosen_pembimbing_id' => 2,
            'judul_tugas_akhir' => 'Sistem Tugas Akhir',
            'status_aktif' => 1,
        ]);
    }
}
