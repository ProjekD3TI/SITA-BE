<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\JabatanDosen;
use Illuminate\Support\Facades\Hash;

class DosenJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        JabatanDosen::truncate();
        Dosen::truncate();
        Jabatan::truncate();

        // Create Jabatan (Positions)
        $jabatan = [
            ['name' => 'Dosen', 'description' => 'Dosen pengajar tetap'],
            ['name' => 'Kalab', 'description' => 'Kepala Laboratorium'],
            ['name' => 'Kaprodi', 'description' => 'Kepala Program Studi'],
            ['name' => 'Petugas', 'description' => 'Petugas Khusus']
        ];

        foreach ($jabatan as $jbt) {
            Jabatan::create($jbt);
        }

        // Get Jabatan IDs for easy reference
        $dosenJabatanId = Jabatan::where('name', 'Dosen')->first()->id;
        $kalabJabatanId = Jabatan::where('name', 'Kalab')->first()->id;
        $kaprodiJabatanId = Jabatan::where('name', 'Kaprodi')->first()->id;
        $petugasJabatanId = Jabatan::where('name', 'Petugas')->first()->id;

        // Create Dosen (Lecturers)
        $dosen = [
            [
                'name' => 'Dr. Budi Santoso, M.Sc.',
                'email' => 'budi.santoso@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19850610001',
                'status' => 1,
                'jabatan' => [$dosenJabatanId, $kalabJabatanId], // Dosen + Kalab
            ],
            [
                'name' => 'Dr. Siti Rahayu, M.Kom.',
                'email' => 'siti.rahayu@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19880715002',
                'status' => 1,
                'jabatan' => [$dosenJabatanId, $kaprodiJabatanId], // Dosen + Kaprodi
            ],
            [
                'name' => 'Ir. Ahmad Wijaya, M.T.',
                'email' => 'ahmad.wijaya@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19790523003',
                'status' => 1,
                'jabatan' => [$dosenJabatanId, $petugasJabatanId], // Dosen + Petugas
            ],
            [
                'name' => 'Dr. Maya Indah, Ph.D.',
                'email' => 'maya.indah@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19830217004',
                'status' => 1,
                'jabatan' => [$dosenJabatanId], // Only Dosen
            ],
            [
                'name' => 'Prof. Eko Prasetyo, M.Eng.',
                'email' => 'eko.prasetyo@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19750408005',
                'status' => 1,
                'jabatan' => [$dosenJabatanId], // Only Dosen
            ],
            [
                'name' => 'Dr. Dewi Sartika, M.Si.',
                'email' => 'dewi.sartika@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19900925006',
                'status' => 1,
                'jabatan' => [$dosenJabatanId], // Only Dosen
            ],
            [
                'name' => 'Dr. Hendra Kusuma, M.Kom.',
                'email' => 'hendra.kusuma@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19820630007',
                'status' => 1,
                'jabatan' => [$dosenJabatanId, $kalabJabatanId], // Dosen + Kalab
            ],
            [
                'name' => 'Dr. Rina Anggraini, M.Pd.',
                'email' => 'rina.anggraini@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19870212008',
                'status' => 1,
                'jabatan' => [$dosenJabatanId, $petugasJabatanId], // Dosen + Petugas
            ],
            [
                'name' => 'Dr. Joko Widodo, M.T.',
                'email' => 'joko.widodo@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19760814009',
                'status' => 1,
                'jabatan' => [$dosenJabatanId], // Only Dosen
            ],
            [
                'name' => 'Dr. Ratna Sari, M.Cs.',
                'email' => 'ratna.sari@example.com',
                'password' => Hash::make('password123'),
                'nik' => '19840509010',
                'status' => 1,
                'jabatan' => [$dosenJabatanId], // Only Dosen
            ],
        ];

        // Create Dosen and their JabatanDosen relations
        foreach ($dosen as $ds) {
            $jabatanIds = $ds['jabatan'];
            unset($ds['jabatan']);
            
            $dosenModel = Dosen::create($ds);
            
            // Assign jabatan to dosen
            foreach ($jabatanIds as $jabatanId) {
                JabatanDosen::create([
                    'dosen_id' => $dosenModel->id,
                    'jabatan_id' => $jabatanId,
                    'is_active' => 1,
                ]);
            }
        }

        $this->command->info('Dosen, Jabatan, and JabatanDosen seeded successfully!');
    }
}