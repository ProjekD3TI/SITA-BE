<?php

namespace Database\Seeders;

use App\Models\RequestPembimbing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequestPembimbingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Raffael
        RequestPembimbing::create([
            'tugas_akhir_id' => 1,
            'dosen_id' => 15,
            'status_request' => 1,
        ]);
        // Raihan
        RequestPembimbing::create([
            'tugas_akhir_id' => 2,
            'dosen_id' => 1,
            'status_request' => 1,
        ]);
        // Rizqi
        RequestPembimbing::create([
            'tugas_akhir_id' => 3,
            'dosen_id' => 2,
            'status_request' => 1,
        ]);
    }
}
