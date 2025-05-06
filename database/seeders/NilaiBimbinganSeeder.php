<?php

namespace Database\Seeders;

use App\Models\NilaiBimbingan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NilaiBimbinganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NilaiBimbingan::create([
            'rerata_bimbingan' => 85,
            'disiplin' => 90,
            'komunikasi' => 80,
            'skill_pengetahuan' => 88,
            'tingkah_laku' => 82,
        ]);

        NilaiBimbingan::create([
            'rerata_bimbingan' => 78,
            'disiplin' => 85,
            'komunikasi' => 75,
            'skill_pengetahuan' => 80,
            'tingkah_laku' => 78,
        ]);
    }
}
