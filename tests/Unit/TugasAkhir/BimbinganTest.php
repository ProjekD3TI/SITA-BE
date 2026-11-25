<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Bimbingan;
use App\Models\TugasAkhir;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BimbinganTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_bimbingan()
    {
        // Test pembuatan instance bimbingan
        $bimbingan = Bimbingan::factory()->create();

        $this->assertInstanceOf(Bimbingan::class, $bimbingan);
        $this->assertDatabaseHas('bimbingan', ['id' => $bimbingan->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $bimbingan = new Bimbingan([
            'tugas_akhir_id' => 1,
            'catatan' => 'Revisi pada bab metodologi',
            'bab' => 'Bab 3',
            'status_verifikasi' => 'disetujui',
            'tanggal_bimbingan' => now(),
        ]);

        $this->assertEquals(1, $bimbingan->tugas_akhir_id);
        $this->assertEquals('Revisi pada bab metodologi', $bimbingan->catatan);
        $this->assertEquals('Bab 3', $bimbingan->bab);
        $this->assertEquals('disetujui', $bimbingan->status_verifikasi);
        $this->assertNotNull($bimbingan->tanggal_bimbingan);
    }

    /** @test */
    public function menggunakan_table_bimbingan()
    {
        // Test model menggunakan table yang benar
        $bimbingan = new Bimbingan();

        $this->assertEquals('bimbingan', $bimbingan->getTable());
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $tugasAkhir = TugasAkhir::factory()->create();
        $bimbingan = Bimbingan::factory()->create([
            'tugas_akhir_id' => $tugasAkhir->id
        ]);

        $this->assertInstanceOf(TugasAkhir::class, $bimbingan->tugasAkhir);
        $this->assertEquals($tugasAkhir->id, $bimbingan->tugasAkhir->id);
    }
}
