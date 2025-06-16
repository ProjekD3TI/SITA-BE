<?php

namespace Database\Seeders;

use App\Models\StatusForm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StatusForm::create([
            'nama_form' => 'Proposal',
            'is_open' => true,
        ]);
        StatusForm::create([
            'nama_form' => 'Tugas Akhir',
            'is_open' => true,
        ]);
    }
}