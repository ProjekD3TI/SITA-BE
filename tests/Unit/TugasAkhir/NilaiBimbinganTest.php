<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\NilaiBimbingan;
use App\Models\TugasAkhir;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiBimbinganTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_nilai_bimbingan()
    {
        // Test pembuatan instance nilai bimbingan
        $nilaiBimbingan = NilaiBimbingan::factory()->create();

        $this->assertInstanceOf(NilaiBimbingan::class, $nilaiBimbingan);
        $this->assertDatabaseHas('nilai_bimbingan', ['id' => $nilaiBimbingan->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $nilaiBimbingan = new NilaiBimbingan([
            'rerata_bimbingan' => 85.5,
            'disiplin' => 80,
            'komunikasi' => 85,
            'skill_pengetahuan' => 90,
            'tingkah_laku' => 88,
        ]);

        $this->assertEquals(85.5, $nilaiBimbingan->rerata_bimbingan);
        $this->assertEquals(80, $nilaiBimbingan->disiplin);
        $this->assertEquals(85, $nilaiBimbingan->komunikasi);
        $this->assertEquals(90, $nilaiBimbingan->skill_pengetahuan);
        $this->assertEquals(88, $nilaiBimbingan->tingkah_laku);
    }

    /** @test */
    public function menggunakan_table_nilai_bimbingan()
    {
        // Test model menggunakan table yang benar
        $nilaiBimbingan = new NilaiBimbingan();

        $this->assertEquals('nilai_bimbingan', $nilaiBimbingan->getTable());
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $nilaiBimbingan = NilaiBimbingan::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'nilai_bimbingan_id' => $nilaiBimbingan->id
        ]);

        $this->assertInstanceOf(TugasAkhir::class, $nilaiBimbingan->tugasAkhir);
    }
}
