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
            'proposal_id' => 1,
            'semester_id' => 1,
            'tahun_ajaran_id' => 4,
            'judul_tugas_akhir' => 'Sistem Informasi Akademik',
            'status_valid' => 1,
            'status_aktif' => 1,
        ]);

        TugasAkhir::create([
            'mahasiswa_id' => 2,
            'dosen_pembimbing_id' => 2,
            'proposal_id' => 2,
            'semester_id' => 2,
            'tahun_ajaran_id' => 4,
            'judul_tugas_akhir' => 'Sistem Tugas Akhir',
            'status_valid' => 2,
            'status_aktif' => 1,
        ]);

        TugasAkhir::create([
            'mahasiswa_id' => 3,
            'proposal_id' => 3,
            'judul_tugas_akhir' => 'Sistem Informasi Perpustakaan',
            'status_aktif' => 1,
        ]);

    }
}
