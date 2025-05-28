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

        // Raffael
        Proposal::create([
            'judul_proposal' => 'Simulasi Mitigasi Bencana Alam AR Update',
            'status_proposal' => 1,
        ]);

        // Raihan
        Proposal::create([
            'judul_proposal' => 'Sistem Informasi Tugas Akhir Update',
            'status_proposal' => 1,
        ]);

        Proposal::create([
            'judul_proposal' => 'Pancing Ikan Otomatis',
            'status_proposal' => 1,
        ]);
    }
}
