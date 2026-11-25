<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\JabatanDosen;
use App\Models\Jabatan;
use App\Models\Dosen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JabatanDosenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_jabatan_dosen()
    {
        // Test pembuatan instance jabatan dosen
        $jabatanDosen = JabatanDosen::factory()->create();

        $this->assertInstanceOf(JabatanDosen::class, $jabatanDosen);
        $this->assertDatabaseHas('jabatan_dosen', ['id' => $jabatanDosen->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $jabatanDosen = new JabatanDosen([
            'dosen_id' => 1,
            'jabatan_id' => 1,
            'is_active' => true,
        ]);

        $this->assertEquals(1, $jabatanDosen->dosen_id);
        $this->assertEquals(1, $jabatanDosen->jabatan_id);
        $this->assertEquals(true, $jabatanDosen->is_active);
    }

    /** @test */
    public function menggunakan_table_jabatan_dosen()
    {
        // Test model menggunakan table yang benar
        $jabatanDosen = new JabatanDosen();

        $this->assertEquals('jabatan_dosen', $jabatanDosen->getTable());
    }

    /** @test */
    public function menggunakan_primary_key_id()
    {
        // Test model menggunakan primary key yang benar
        $jabatanDosen = new JabatanDosen();

        $this->assertEquals('id', $jabatanDosen->getKeyName());
    }

    /** @test */
    public function memiliki_relasi_jabatan()
    {
        // Test relasi dengan Jabatan
        $jabatan = Jabatan::factory()->create();
        $dosen = Dosen::factory()->create();
        $jabatanDosen = JabatanDosen::factory()->create([
            'jabatan_id' => $jabatan->id,
            'dosen_id' => $dosen->id
        ]);

        $this->assertInstanceOf(Jabatan::class, $jabatanDosen->jabatan);
        $this->assertEquals($jabatan->id, $jabatanDosen->jabatan->id);
    }

    /** @test */
    public function memiliki_relasi_dosen()
    {
        // Test relasi dengan Dosen
        $jabatan = Jabatan::factory()->create();
        $dosen = Dosen::factory()->create();
        $jabatanDosen = JabatanDosen::factory()->create([
            'jabatan_id' => $jabatan->id,
            'dosen_id' => $dosen->id
        ]);

        $this->assertInstanceOf(Dosen::class, $jabatanDosen->dosen);
        $this->assertEquals($dosen->id, $jabatanDosen->dosen->id);
    }
}
