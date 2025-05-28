<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\DosenPenguji;
use App\Models\TugasAkhir;
use App\Models\Dosen;
use App\Models\NilaiUjianTA;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DosenPengujiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_dosen_penguji()
    {
        // Test pembuatan instance dosen penguji
        $dosenPenguji = DosenPenguji::factory()->create();

        $this->assertInstanceOf(DosenPenguji::class, $dosenPenguji);
        $this->assertDatabaseHas('dosen_penguji', [
            'dosen_id' => $dosenPenguji->dosen_id,
            'tugas_akhir_id' => $dosenPenguji->tugas_akhir_id
        ]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $dosenPenguji = new DosenPenguji([
            'dosen_id' => 1,
            'tugas_akhir_id' => 1,
            'nilai_ujian_ta_id' => 1,
            'jabatan_penguji' => 'ketua',
            'revisi' => 'Perbaikan pada metodologi',
            'catatan_semhas' => 'Presentasi cukup baik',
        ]);

        $this->assertEquals(1, $dosenPenguji->dosen_id);
        $this->assertEquals(1, $dosenPenguji->tugas_akhir_id);
        $this->assertEquals(1, $dosenPenguji->nilai_ujian_ta_id);
        $this->assertEquals('ketua', $dosenPenguji->jabatan_penguji);
        $this->assertEquals('Perbaikan pada metodologi', $dosenPenguji->revisi);
        $this->assertEquals('Presentasi cukup baik', $dosenPenguji->catatan_semhas);
    }

    /** @test */
    public function menggunakan_table_dosen_penguji()
    {
        // Test model menggunakan table yang benar
        $dosenPenguji = new DosenPenguji();

        $this->assertEquals('dosen_penguji', $dosenPenguji->getTable());
    }

    /** @test */
    public function memiliki_timestamps()
    {
        // Test model memiliki timestamps
        $dosenPenguji = new DosenPenguji();

        $this->assertTrue($dosenPenguji->timestamps);
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $tugasAkhir = TugasAkhir::factory()->create();
        $dosen = Dosen::factory()->create();
        $dosenPenguji = DosenPenguji::factory()->create([
            'tugas_akhir_id' => $tugasAkhir->id,
            'dosen_id' => $dosen->id
        ]);

        $this->assertInstanceOf(TugasAkhir::class, $dosenPenguji->tugasAkhir);
        $this->assertEquals($tugasAkhir->id, $dosenPenguji->tugasAkhir->id);
    }

    /** @test */
    public function memiliki_relasi_dosen()
    {
        // Test relasi dengan Dosen
        $tugasAkhir = TugasAkhir::factory()->create();
        $dosen = Dosen::factory()->create();
        $dosenPenguji = DosenPenguji::factory()->create([
            'tugas_akhir_id' => $tugasAkhir->id,
            'dosen_id' => $dosen->id
        ]);

        $this->assertInstanceOf(Dosen::class, $dosenPenguji->dosen);
        $this->assertEquals($dosen->id, $dosenPenguji->dosen->id);
    }

    /** @test */
    public function memiliki_relasi_nilai_ujian_ta()
    {
        // Test relasi dengan NilaiUjianTA
        $nilaiUjianTA = NilaiUjianTA::factory()->create();
        $dosenPenguji = DosenPenguji::factory()->create([
            'nilai_ujian_ta_id' => $nilaiUjianTA->id
        ]);

        $this->assertInstanceOf(NilaiUjianTA::class, $dosenPenguji->nilaiUjianTA);
        $this->assertEquals($nilaiUjianTA->id, $dosenPenguji->nilaiUjianTA->id);
    }
}
