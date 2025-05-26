<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            TahunAjaranSeeder::class,
            SesiSeeder::class,
            JabatanSeeder::class,
            AdminSeeder::class,
            DosenSeeder::class,
            UserSeeder::class,
            JabatanDosenSeeder::class,
            SemesterSeeder::class,
            RuangSeeder::class,
            KategoriSeeder::class,
            ProposalSeeder::class,
            TugasAkhirSeeder::class,
            NilaiBimbinganSeeder::class,
            BimbinganSeeder::class,
            RequestPembimbingSeeder::class,
            NilaiUjianTASeeder::class,
            DosenPengujiSeeder::class,
            UjianTASeeder::class,
            StatusFormSeeder::class,
        ]);
    }
}
