<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        Admin::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin_ta',
        ]);

        Admin::factory()->create([
            'name' => 'Agus Salim',
            'email' => 'agus@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin_pengelola',
        ]);
    }
}
