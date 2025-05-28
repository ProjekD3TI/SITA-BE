<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Jabatan;
use App\Models\Dosen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JabatanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_jabatan()
    {
        // Test pembuatan instance jabatan
        $jabatan = Jabatan::factory()->create();

        $this->assertInstanceOf(Jabatan::class, $jabatan);
        $this->assertDatabaseHas('jabatan', ['id' => $jabatan->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $jabatan = new Jabatan([
            'name' => 'Ketua Program Studi',
            'description' => 'Bertanggung jawab atas program studi',
        ]);

        $this->assertEquals('Ketua Program Studi', $jabatan->name);
        $this->assertEquals('Bertanggung jawab atas program studi', $jabatan->description);
    }

    /** @test */
    public function menggunakan_table_jabatan()
    {
        // Test model menggunakan table yang benar
        $jabatan = new Jabatan();

        $this->assertEquals('jabatan', $jabatan->getTable());
    }

    /** @test */
    public function menggunakan_primary_key_id()
    {
        // Test model menggunakan primary key yang benar
        $jabatan = new Jabatan();

        $this->assertEquals('id', $jabatan->getKeyName());
    }

    /** @test */
    public function memiliki_relasi_dosen()
    {
        // Test relasi dengan Dosen
        $jabatan = Jabatan::factory()->create();
        $dosen = Dosen::factory()->create();

        $jabatan->dosen()->attach($dosen->id);

        $this->assertTrue($jabatan->dosen->contains($dosen));
        $this->assertInstanceOf(Dosen::class, $jabatan->dosen->first());
    }
}
