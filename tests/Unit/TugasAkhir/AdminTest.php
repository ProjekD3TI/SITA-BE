<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_admin()
    {
        // Test pembuatan instance admin
        $admin = Admin::factory()->create();

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertDatabaseHas('admin', ['id' => $admin->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $admin = new Admin([
            'name' => 'Administrator',
            'email' => 'admin@staff.uns.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin_ta',
        ]);

        $this->assertEquals('Administrator', $admin->name);
        $this->assertEquals('admin@staff.uns.ac.id', $admin->email);
        $this->assertEquals('admin_ta', $admin->role);
    }

    /** @test */
    public function menyembunyikan_atribut_password()
    {
        // Test password disembunyikan dalam response
        $admin = Admin::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $data = $admin->toArray();

        $this->assertArrayNotHasKey('password', $data);
    }

    /** @test */
    public function password_dienkripsi_dengan_benar()
    {
        // Test password di-hash dengan benar
        $plainPassword = 'password123';

        $admin = Admin::factory()->create([
            'password' => bcrypt($plainPassword)
        ]);

        $this->assertTrue(Hash::check($plainPassword, $admin->password));
    }

    /** @test */
    public function menggunakan_table_admin()
    {
        // Test model menggunakan table yang benar
        $admin = new Admin();

        $this->assertEquals('admin', $admin->getTable());
    }

    /** @test */
    public function menggunakan_primary_key_id()
    {
        // Test model menggunakan primary key yang benar
        $admin = new Admin();

        $this->assertEquals('id', $admin->getKeyName());
    }
}
