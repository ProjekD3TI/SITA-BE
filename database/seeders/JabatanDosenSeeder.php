<?php

namespace Database\Seeders;

use App\Models\JabatanDosen;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanDosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatan_dosens = [
            ['dosen_id' => 1 ,'jabatan_id' => 1, 'is_active' => 0],

        ];

        foreach ($jabatan_dosens as $jabatan_dosen) {
            JabatanDosen::create($jabatan_dosen);
        }
    }
}