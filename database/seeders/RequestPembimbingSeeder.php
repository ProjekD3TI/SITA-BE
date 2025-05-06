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
        RequestPembimbing::create([
            'tugas_akhir_id' => 3,
            'dosen_id' => 3,
            'status_request' => 0,
        ]);
    }
}
