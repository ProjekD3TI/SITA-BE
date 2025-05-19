<?php

namespace Database\Seeders;

use App\Models\Proposal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProposalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Proposal::create([
            'judul_proposal' => 'Sistem Informasi Akademik',
            'status_proposal' => 1,
        ]);

        Proposal::create([
            'judul_proposal' => 'Sistem Tugas Akhir',
            'status_proposal' => 1,
        ]);

        Proposal::create([
            'judul_proposal' => 'Sistem Informasi Perpustakaan',
            'status_proposal' => 1,
        ]);
    }
}
