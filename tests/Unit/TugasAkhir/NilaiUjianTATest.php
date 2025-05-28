<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\NilaiUjianTA;
use App\Models\DosenPenguji;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiUjianTATest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_nilai_ujian_ta()
    {
        // Test pembuatan instance nilai ujian TA
        $nilaiUjianTA = NilaiUjianTA::factory()->create();

        $this->assertInstanceOf(NilaiUjianTA::class, $nilaiUjianTA);
        $this->assertDatabaseHas('nilai_ujian_ta', ['id' => $nilaiUjianTA->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $nilaiUjianTA = new NilaiUjianTA([
            'kualitas_tata_laporan' => 85,
            'kemampuan_teknis' => 88,
            'kualitas_produk' => 90,
            'presentasi' => 87,
            'rerata_ujian_ta' => 87.5,
        ]);

        $this->assertEquals(85, $nilaiUjianTA->kualitas_tata_laporan);
        $this->assertEquals(88, $nilaiUjianTA->kemampuan_teknis);
        $this->assertEquals(90, $nilaiUjianTA->kualitas_produk);
        $this->assertEquals(87, $nilaiUjianTA->presentasi);
        $this->assertEquals(87.5, $nilaiUjianTA->rerata_ujian_ta);
    }

    /** @test */
    public function menggunakan_table_nilai_ujian_ta()
    {
        // Test model menggunakan table yang benar
        $nilaiUjianTA = new NilaiUjianTA();

        $this->assertEquals('nilai_ujian_ta', $nilaiUjianTA->getTable());
    }

    /** @test */
    public function memiliki_relasi_dosen_penguji()
    {
        // Test relasi dengan DosenPenguji
        $nilaiUjianTA = NilaiUjianTA::factory()->create();
        $dosenPenguji = DosenPenguji::factory()->create([
            'nilai_ujian_ta_id' => $nilaiUjianTA->id
        ]);

        $this->assertInstanceOf(DosenPenguji::class, $nilaiUjianTA->dosenPenguji);
    }
}
