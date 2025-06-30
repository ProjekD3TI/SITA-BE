<?php

namespace Database\Seeders;

use App\Models\UjianTA;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UjianTASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Raffael
        UjianTA::create([
            'tugas_akhir_id' => 1,
            'tanggal_ujian' => '2025-10-01',
            'ruang_id' => 1,
            'sesi_id' => 1,
            'semester_id' => 1,
            'tahun_ajaran_id' => 1,
            'status_ujian' => 'aktif',
            'status_kelulusan' => 1,
            'edit_nilai' => false,
        ]);
    }
}
