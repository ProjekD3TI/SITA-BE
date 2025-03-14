<?php

namespace Database\Seeders;

use App\Models\Sesi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SesiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $sesi_data = [
            [
                "nama_sesi" => "Sesi 1",
                "waktu_mulai" => "08:00:00",
                "waktu_selesai" => "10:00:00"
            ],
            [
                "nama_sesi" => "Sesi 2",
                "waktu_mulai" => "10:00:00",
                "waktu_selesai" => "12:00:00"
            ],
            [
                "nama_sesi" => "Sesi 3",
                "waktu_mulai" => "13:00:00",
                "waktu_selesai" => "15:00:00"
            ]
            ,
            [
                "nama_sesi" => "Sesi 4",
                "waktu_mulai" => "15:00:00",
                "waktu_selesai" => "17:00:00"
            ]
        ];

        foreach ($sesi_data as $data) {
            Sesi::create($data);
        }
    }
}
