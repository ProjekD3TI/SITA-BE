<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProposalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('proposal')->insert([
            [
                'judul_proposal' => 'Analisis Algoritma Sorting',
                'file_proposal' => 'proposal1.pdf',
                'file_transkrip' => 'transkrip1.pdf',
                'status_proposal' => 0,
                'tanggal_unggah' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul_proposal' => 'Pengembangan Aplikasi Mobile',
                'file_proposal' => 'proposal2.pdf',
                'file_transkrip' => 'transkrip2.pdf',
                'status_proposal' => 0,
                'tanggal_unggah' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul_proposal' => 'Sistem Keamanan Jaringan',
                'file_proposal' => 'proposal3.pdf',
                'file_transkrip' => 'transkrip3.pdf',
                'status_proposal' => 0,
                'tanggal_unggah' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul_proposal' => 'Pemrograman Berbasis Web',
                'file_proposal' => 'proposal4.pdf',
                'file_transkrip' => 'transkrip4.pdf',
                'status_proposal' => 0,
                'tanggal_unggah' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul_proposal' => 'Analisis Big Data',
                'file_proposal' => 'proposal5.pdf',
                'file_transkrip' => 'transkrip5.pdf',
                'status_proposal' => 0,
                'tanggal_unggah' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
