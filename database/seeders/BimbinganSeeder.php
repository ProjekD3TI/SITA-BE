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
        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 1 selesai',
            'bab' => 1,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);

        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 1 revisi',
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
            'catatan' => 'Bab 2 revisi',
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
            'catatan' => 'Bab 3 revisi',
            'bab' => 3,
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
            'catatan' => 'Bab 4 revisi',
            'bab' => 4,
            'status_verifikasi' => 1,
            'tanggal_bimbingan' => now(),
        ]);

        Bimbingan::create([
            'tugas_akhir_id' => 1,
            'catatan' => 'Bab 5 selesai',
            'bab' => 5,
            'status_verifikasi' => 0,
            'tanggal_bimbingan' => now(),
        ]);


    }
}
