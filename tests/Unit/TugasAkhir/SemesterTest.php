<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Semester;
use App\Models\TugasAkhir;
use App\Models\UjianTA;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_semester()
    {
        // Test pembuatan instance semester
        $semester = Semester::factory()->create();

        $this->assertInstanceOf(Semester::class, $semester);
        $this->assertDatabaseHas('semester', ['id' => $semester->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $semester = new Semester([
            'nama_semester' => 'Semester Genap 2023/2024',
            'is_default' => true,
        ]);

        $this->assertEquals('Semester Genap 2023/2024', $semester->nama_semester);
        $this->assertEquals(true, $semester->is_default);
    }

    /** @test */
    public function menggunakan_table_semester()
    {
        // Test model menggunakan table yang benar
        $semester = new Semester();

        $this->assertEquals('semester', $semester->getTable());
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $semester = Semester::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'semester_id' => $semester->id
        ]);

        $this->assertTrue($semester->tugasAkhir->contains($tugasAkhir));
        $this->assertInstanceOf(TugasAkhir::class, $semester->tugasAkhir->first());
    }

    /** @test */
    public function memiliki_relasi_ujian_ta()
    {
        // Test relasi dengan UjianTA
        $semester = Semester::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'semester_id' => $semester->id
        ]);

        $this->assertTrue($semester->ujianTA->contains($ujianTA));
        $this->assertInstanceOf(UjianTA::class, $semester->ujianTA->first());
    }
}
