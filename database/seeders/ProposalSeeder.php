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
            'file_proposal' => 'proposal_1.pdf',
            'file_transkrip' => 'transkrip_1.pdf',
            'status_proposal' => 1,
        ]);

        Proposal::create([
            'judul_proposal' => 'Sistem Tugas Akhir',
            'file_proposal' => 'proposal_2.pdf',
            'file_transkrip' => 'transkrip_2.pdf',
            'status_proposal' => 1,
        ]);

        Proposal::create([
            'judul_proposal' => 'Sistem Informasi Perpustakaan',
            'file_proposal' => 'proposal_3.pdf',
            'file_transkrip' => 'transkrip_3.pdf',
            'status_proposal' => 1,
        ]);
    }
}
