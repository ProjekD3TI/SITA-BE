<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\UjianTA;
use App\Models\TugasAkhir;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\Ruang;
use App\Models\Sesi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UjianTATest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_ujian_ta()
    {
        // Test pembuatan instance ujian TA
        $ujianTA = UjianTA::factory()->create();

        $this->assertInstanceOf(UjianTA::class, $ujianTA);
        $this->assertDatabaseHas('ujian_ta', ['id' => $ujianTA->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $tanggalUjian = now()->addWeek();

        $ujianTA = new UjianTA([
            'tugas_akhir_id' => 1,
            'tanggal_ujian' => $tanggalUjian,
            'ruang_id' => 1,
            'sesi_id' => 1,
            'semester_id' => 1,
            'tahun_ajaran_id' => 1,
            'status_ujian' => 'terjadwal',
        ]);

        $this->assertEquals(1, $ujianTA->tugas_akhir_id);
        $this->assertEquals($tanggalUjian, $ujianTA->tanggal_ujian);
        $this->assertEquals(1, $ujianTA->ruang_id);
        $this->assertEquals(1, $ujianTA->sesi_id);
        $this->assertEquals(1, $ujianTA->semester_id);
        $this->assertEquals(1, $ujianTA->tahun_ajaran_id);
        $this->assertEquals('terjadwal', $ujianTA->status_ujian);
    }

    /** @test */
    public function menggunakan_table_ujian_ta()
    {
        // Test model menggunakan table yang benar
        $ujianTA = new UjianTA();

        $this->assertEquals('ujian_ta', $ujianTA->getTable());
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $tugasAkhir = TugasAkhir::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'tugas_akhir_id' => $tugasAkhir->id
        ]);

        $this->assertInstanceOf(TugasAkhir::class, $ujianTA->tugasAkhir);
        $this->assertEquals($tugasAkhir->id, $ujianTA->tugasAkhir->id);
    }

    /** @test */
    public function memiliki_relasi_tahun_ajaran()
    {
        // Test relasi dengan TahunAjaran
        $tahunAjaran = TahunAjaran::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'tahun_ajaran_id' => $tahunAjaran->id
        ]);

        $this->assertInstanceOf(TahunAjaran::class, $ujianTA->tahunAjaran);
        $this->assertEquals($tahunAjaran->id, $ujianTA->tahunAjaran->id);
    }

    /** @test */
    public function memiliki_relasi_semester()
    {
        // Test relasi dengan Semester
        $semester = Semester::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'semester_id' => $semester->id
        ]);

        $this->assertInstanceOf(Semester::class, $ujianTA->semester);
        $this->assertEquals($semester->id, $ujianTA->semester->id);
    }

    /** @test */
    public function memiliki_relasi_ruang()
    {
        // Test relasi dengan Ruang
        $ruang = Ruang::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'ruang_id' => $ruang->id
        ]);

        $this->assertInstanceOf(Ruang::class, $ujianTA->ruang);
        $this->assertEquals($ruang->id, $ujianTA->ruang->id);
    }

    /** @test */
    public function memiliki_relasi_sesi()
    {
        // Test relasi dengan Sesi
        $sesi = Sesi::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'sesi_id' => $sesi->id
        ]);

        $this->assertInstanceOf(Sesi::class, $ujianTA->sesi);
        $this->assertEquals($sesi->id, $ujianTA->sesi->id);
    }
}
