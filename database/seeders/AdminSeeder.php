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
            'name' => 'Nurlaily Purnama Sari, S.Kom.',
            'email' => 'sari@staff.uns.ac.id',
            'password' => bcrypt('123123123'),
            'role' => 'admin_ta',
        ]);
        Admin::factory(10)->create();
    }
}
