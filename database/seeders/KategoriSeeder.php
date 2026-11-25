<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $kategori_data = [
            [
                "nama_kategori" => "Internet of Things",
            ],
            [
                "nama_kategori" => "Sistem Informasi",
            ],
            [
                "nama_kategori" => "Game Development",
            ],
            [
                "nama_kategori" => "Augmented Reality",
            ],
            [
                "nama_kategori" => "Virtual Reality",
            ]
        ];
        foreach ($kategori_data as $data) {
            Kategori::create($data);
        }
    }
}
