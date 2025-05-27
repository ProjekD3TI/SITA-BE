<?php

namespace Database\Seeders;

use App\Models\Berkas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BerkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Raffael
        // Proposal
        Berkas::create([
            'berkasable_type' => 'App\Models\Proposal',
            'berkasable_id' => 1,
            'nama_berkas' => 'Naskah Proposal Tugas Akhir',
            'file_path' => '/private/proposal/1/Naskah_Proposal_TA_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\Proposal',
            'berkasable_id' => 1,
            'nama_berkas' => 'Transkrip Nilai Mahasiswa',
            'file_path' => '/private/proposal/1/Transkrip_Nilai_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);

        // Tugas Akhir
        Berkas::create([
            'berkasable_type' => 'App\Models\TugasAkhir',
            'berkasable_id' => 1,
            'nama_berkas' => 'Naskah Tugas Akhir',
            'file_path' => '/private/tugas_akhir/1/Naskah_TA_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\TugasAkhir',
            'berkasable_id' => 1,
            'nama_berkas' => 'Scan KRS Semester Akhir',
            'file_path' => '/private/tugas_akhir/1/KRS_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\TugasAkhir',
            'berkasable_id' => 1,
            'nama_berkas' => 'Bukti Persetujuan Pembimbing',
            'file_path' => '/private/tugas_akhir/1/Persetujuan_Pembimbing_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\TugasAkhir',
            'berkasable_id' => 1,
            'nama_berkas' => 'Transkrip Nilai Mahasiswa',
            'file_path' => '/private/tugas_akhir/1/Transkrip_Nilai_UjianTA_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\TugasAkhir',
            'berkasable_id' => 1,
            'nama_berkas' => 'Bukti Sertifikat Kompetensi',
            'file_path' => '/private/tugas_akhir/1/SertifikatKompetensi_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\TugasAkhir',
            'berkasable_id' => 1,
            'nama_berkas' => 'Sertifikat TOEIC',
            'file_path' => '/private/tugas_akhir/1/TOEIC_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\TugasAkhir',
            'berkasable_id' => 1,
            'nama_berkas' => 'Berita Acara Seminar Hasil',
            'file_path' => '/private/tugas_akhir/1/BeritaAcaraSemhas_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\TugasAkhir',
            'berkasable_id' => 1,
            'nama_berkas' => 'Surat Perpanjangan Masa Studi',
            'file_path' => '/private/tugas_akhir/1/SuratPerpanjanganStudi_Raffael_V3423070.pdf',
            'status_valid' => 1,
        ]);

        // Raihan
        // Proposal
        Berkas::create([
            'berkasable_type' => 'App\Models\Proposal',
            'berkasable_id' => 2,
            'nama_berkas' => 'Naskah Proposal Tugas Akhir',
            'file_path' => '/private/proposal/2/Naskah_Proposal_TA_Raihan_V3423072.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\Proposal',
            'berkasable_id' => 2,
            'nama_berkas' => 'Transkrip Nilai Mahasiswa',
            'file_path' => '/private/proposal/2/Transkrip_Nilai_Raihan_V3423072.pdf',
            'status_valid' => 1,
        ]);

        // Rizqi
        // Proposal
        Berkas::create([
            'berkasable_type' => 'App\Models\Proposal',
            'berkasable_id' => 3,
            'nama_berkas' => 'Naskah Proposal Tugas Akhir',
            'file_path' => '/private/proposal/2/Naskah_Proposal_TA_Riqzi_V3423077.pdf',
            'status_valid' => 1,
        ]);
        Berkas::create([
            'berkasable_type' => 'App\Models\Proposal',
            'berkasable_id' => 3,
            'nama_berkas' => 'Transkrip Nilai Mahasiswa',
            'file_path' => '/private/proposal/2/Transkrip_Nilai_Rizqi_V3423077.pdf',
            'status_valid' => 1,
        ]);
    }
}