<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\TahunAjaran;
use App\Models\TugasAkhir;
use App\Models\UjianTA;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TahunAjaranTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_tahun_ajaran()
    {
        // Test pembuatan instance tahun ajaran
        $tahunAjaran = TahunAjaran::factory()->create();

        $this->assertInstanceOf(TahunAjaran::class, $tahunAjaran);
        $this->assertDatabaseHas('tahun_ajaran', ['id' => $tahunAjaran->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $tahunAjaran = new TahunAjaran([
            'tahun_ajaran' => '2023/2024',
            'is_default' => true,
        ]);

        $this->assertEquals('2023/2024', $tahunAjaran->tahun_ajaran);
        $this->assertEquals(true, $tahunAjaran->is_default);
    }

    /** @test */
    public function menggunakan_table_tahun_ajaran()
    {
        // Test model menggunakan table yang benar
        $tahunAjaran = new TahunAjaran();

        $this->assertEquals('tahun_ajaran', $tahunAjaran->getTable());
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $tahunAjaran = TahunAjaran::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'tahun_ajaran_id' => $tahunAjaran->id
        ]);

        $this->assertTrue($tahunAjaran->tugasAkhir->contains($tugasAkhir));
        $this->assertInstanceOf(TugasAkhir::class, $tahunAjaran->tugasAkhir->first());
    }

    /** @test */
    public function memiliki_relasi_ujian_ta()
    {
        // Test relasi dengan UjianTA
        $tahunAjaran = TahunAjaran::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'tahun_ajaran_id' => $tahunAjaran->id
        ]);

        $this->assertTrue($tahunAjaran->ujianTA->contains($ujianTA));
        $this->assertInstanceOf(UjianTA::class, $tahunAjaran->ujianTA->first());
    }
}
