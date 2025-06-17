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
            ['dosen_id' => 1, 'jabatan_id' => 3, 'is_active' => 1],
            ['dosen_id' => 1, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 2, 'jabatan_id' => 5, 'is_active' => 1],
            ['dosen_id' => 2, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 3, 'jabatan_id' => 2, 'is_active' => 0],
            ['dosen_id' => 3, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 4, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 5, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 6, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 7, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 8, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 9, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 10, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 11, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 12, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 13, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 14, 'jabatan_id' => 1, 'is_active' => 0],
            ['dosen_id' => 15, 'jabatan_id' => 1, 'is_active' => 0],

        ];

        foreach ($jabatan_dosens as $jabatan_dosen) {
            JabatanDosen::create($jabatan_dosen);
        }
    }
}
