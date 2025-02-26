<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            ['name' => 'Dosen', 'description' => 'Jabatan Dosen biasa'],
            ['name' => 'Kalab', 'description' => 'Kepala Laboratorium'],
            ['name' => 'Kaprodi', 'description' => 'Kepala Program Studi'],
            ['name' => 'Petugas', 'description' => 'Petugas Pelaporan'],
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::create($jabatan);
        }
    }
}
