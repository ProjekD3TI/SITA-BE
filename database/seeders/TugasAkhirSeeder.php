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
        // Raffael
        TugasAkhir::create([
            'mahasiswa_id' => 1,
            'dosen_pembimbing_id' => 15,
            'proposal_id' => 1,
            'semester_id' => 1,
            'tahun_ajaran_id' => 4,
            'nilai_bimbingan_id' => 1,
            'kategori_id' => 4,
            'judul_tugas_akhir' => 'Simulasi Mitigasi Bencana Alam AR Finale Update',
            'nilai_proposal' => 90,
            'nilai_akhir' => 89,
            'catatan_proposal' => 'Lulus dengan baik',
            'status_kelulusan' => 1,
            'status_valid' => 1,
            'status_aktif' => 1,
        ]);
        // Raihan
        TugasAkhir::create([
            'mahasiswa_id' => 2,
            'dosen_pembimbing_id' => 1,
            'proposal_id' => 2,
            'semester_id' => 1,
            'tahun_ajaran_id' => 4,
            'nilai_bimbingan_id' => null,
            'kategori_id' => 4,
            'judul_tugas_akhir' => null,
            'nilai_proposal' => 90,
            'nilai_akhir' => 89,
            'catatan_proposal' => 'Lulus dengan baik',
            'status_valid' => null,
            'status_aktif' => 1,
        ]);
        // Rizqi
        TugasAkhir::create([
            'mahasiswa_id' => 3,
            'dosen_pembimbing_id' => 2,
            'proposal_id' => 3,
            'semester_id' => 1,
            'tahun_ajaran_id' => 4,
            'nilai_bimbingan_id' => null,
            'kategori_id' => 1,
            'judul_tugas_akhir' => null,
            'nilai_proposal' => null,
            'nilai_akhir' => null,
            'catatan_proposal' => null,
            'status_valid' => null,
            'status_aktif' => 1,
        ]);
    }
}
