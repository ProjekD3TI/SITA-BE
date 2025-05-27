<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Dosen::create([
            'name' => 'Eko Harry Pratisto, S.T., M.Info.Tech., Ph.D.',
            'email' => 'ekoharry@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0624118101',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => 'Nanang Maulana Yoeseph, S.Si., M.Cs.',
            'email' => 'nanang.my@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0014078105',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => 'Nurul Firdaus, S.Kom., M.Info.Tech.',
            'email' => 'nurul.firdaus@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0023099106',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => 'Agus Purbayu, S.Si.,M.Kom.',
            'email' => 'bayoe@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0629088001',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Fiddin Yusfida A'La, S.T., M.Eng.",
            'email' => 'fiddin@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0019099304',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Taufiqurrakhman Nur Hidayat, S.Kom., M.Cs.",
            'email' => 'taufiqurrakhman.nh@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0022058207',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Hartatik, S.Si., M.Si.",
            'email' => 'hartatik119@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0703057802',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Mohammad Asrie Safi’i S.Si., M.Kom.",
            'email' => 'safiie99@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0003118106',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Abdul Aziz, S.Kom., M.Cs.",
            'email' => 'aaziz@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0013048101',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Fendi Aji Purnomo, S.SI., M.Eng.",
            'email' => 'fendi_aji@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0026098406',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Rudi Hartono, S.Si., M.Eng.",
            'email' => 'rudi.hartono@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0026128404',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Berliana Kusuma Riasti, S.T., M.Eng.",
            'email' => 'berliana@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0621038101',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Sahirul Alim Tri Bawono, S.Kom., M.Eng.",
            'email' => 'sahirul@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0015028704',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Ovide Decroly Wisnu Ardhi, S.T., M.Eng.",
            'email' => 'ovide@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0603058601',
            'status' => 1,
        ]);
        Dosen::create([
            'name' => "Yudho Yudhanto, S.Kom., M.Kom.",
            'email' => 'yyudhanto@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'nik' => '0005067912',
            'status' => 1,
        ]);
        // Dosen::factory(5)->create();

    }
}