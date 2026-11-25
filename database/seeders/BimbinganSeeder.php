<?php

namespace Database\Seeders;

use App\Models\Bimbingan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BimbinganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Raffael
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 1 selesai',
            'bab' => 1,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 2 selesai',
            'bab' => 2,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 3 selesai',
            'bab' => 3,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 4 dalam perbaikan',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 4 dalam perbaikan',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 4 dalam perbaikan',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 4 selesai',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 5 dalam perbaikan',
            'bab' => 5,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 5 dalam perbaikan',
            'bab' => 5,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 5 selesai',
            'bab' => 5,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);


        // Raihan
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 1 selesai',
            'bab' => 1,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 2 selesai',
            'bab' => 2,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 3 selesai',
            'bab' => 3,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 4 dalam perbaikan',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 4 dalam perbaikan',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 4 dalam perbaikan',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 4 selesai',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 5 dalam perbaikan',
            'bab' => 5,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 5 dalam perbaikan',
            'bab' => 5,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
        Bimbingan::create([
            'tugas_akhir_id' => 2,
            'catatan' => 'Bab 5 selesai',
            'bab' => 5,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);

        // Rizqi
        Bimbingan::create([
            'tugas_akhir_id' => 3,
            'catatan' => 'Andri nGAWII',
            'bab' => 1,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);
    }
}
