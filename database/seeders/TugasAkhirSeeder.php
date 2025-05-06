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
            'krs' => 'krs_1.pdf',
            'persetujuan_pembimbing' => 'persetujuan_1.pdf',
            'sertifikat_kompetensi' => 'sertifikat_kompetensi_1.pdf',
            'toeic' => 'toeic_1.pdf',
            'berita_acara_semhas' => 'berita_acara_semhas_1.pdf',
            'naskah_ta' => 'naskah_ta_1.pdf',
            'masa_perpanjangan_studi' => 'masa_perpanjangan_studi_1.pdf',
            'file_transkrip' => 'transkrip_1.pdf',
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
            'krs' => 'krs_2.pdf',
            'persetujuan_pembimbing' => 'persetujuan_2.pdf',
            'sertifikat_kompetensi' => 'sertifikat_kompetensi_2.pdf',
            'toeic' => 'toeic_2.pdf',
            'berita_acara_semhas' => 'berita_acara_semhas_2.pdf',
            'naskah_ta' => 'naskah_ta_2.pdf',
            'masa_perpanjangan_studi' => 'masa_perpanjangan_studi_2.pdf',
            'file_transkrip' => 'transkrip_2.pdf',
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
