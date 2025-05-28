<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\User;
use App\Models\TugasAkhir;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_mahasiswa()
    {
        // Test pembuatan instance mahasiswa
        $mahasiswa = User::factory()->create();

        $this->assertInstanceOf(User::class, $mahasiswa);
        $this->assertDatabaseHas('mahasiswa', ['id' => $mahasiswa->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $mahasiswa = new User([
            'nim' => '111111',
            'name' => 'Mahasiswa Test',
            'email' => 'mahasiswa@student.uns.ac.id',
            'no_telp' => '08123456789',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->assertEquals('111111', $mahasiswa->nim);
        $this->assertEquals('Mahasiswa Test', $mahasiswa->name);
        $this->assertEquals('mahasiswa@student.uns.ac.id', $mahasiswa->email);
        $this->assertEquals('08123456789', $mahasiswa->no_telp);
        $this->assertEquals(1, $mahasiswa->status);
    }

    /** @test */
    public function menyembunyikan_atribut_password_dan_token()
    {
        // Test password dan remember_token disembunyikan dalam response
        $mahasiswa = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $data = $mahasiswa->toArray();

        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
    }

    /** @test */
    public function password_dienkripsi_dengan_benar()
    {
        // Test password di-hash dengan benar
        $plainPassword = 'password123';

        $mahasiswa = User::factory()->create([
            'password' => bcrypt($plainPassword)
        ]);

        $this->assertTrue(Hash::check($plainPassword, $mahasiswa->password));
    }

    /** @test */
    public function menggunakan_table_mahasiswa()
    {
        // Test model menggunakan table yang benar
        $mahasiswa = new User();

        $this->assertEquals('mahasiswa', $mahasiswa->getTable());
    }
}
