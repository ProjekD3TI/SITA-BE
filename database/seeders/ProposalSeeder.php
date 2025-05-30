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
            'catatan_perbaikan' => 'Kesalahan pada penulisan judul sudah diperbaiki.',
        ]);

        // Raihan
        Proposal::create([
            'judul_proposal' => 'Sistem Informasi Tugas Akhir Update',
            'status_proposal' => 1,
            'catatan_perbaikan' => 'Sudah diperbaiki sesuai dengan masukan dari dosen pembimbing.',
        ]);

        Proposal::create([
            'judul_proposal' => 'Pancing Ikan Otomatis',
            'status_proposal' => 1,
            'catatan_perbaikan' => 'Sudah diperbaiki sesuai dengan masukan dari dosen pembimbing.',
        ]);
    }
}
