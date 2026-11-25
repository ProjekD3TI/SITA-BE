<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Ruang;
use App\Models\UjianTA;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuangTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_ruang()
    {
        // Test pembuatan instance ruang
        $ruang = Ruang::factory()->create();

        $this->assertInstanceOf(Ruang::class, $ruang);
        $this->assertDatabaseHas('ruang', ['id' => $ruang->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $ruang = new Ruang([
            'nama_ruang' => 'Ruang Seminar A',
        ]);

        $this->assertEquals('Ruang Seminar A', $ruang->nama_ruang);
    }

    /** @test */
    public function menggunakan_table_ruang()
    {
        // Test model menggunakan table yang benar
        $ruang = new Ruang();

        $this->assertEquals('ruang', $ruang->getTable());
    }

    /** @test */
    public function memiliki_relasi_ujian_ta()
    {
        // Test relasi dengan UjianTA
        $ruang = Ruang::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'ruang_id' => $ruang->id
        ]);

        $this->assertTrue($ruang->ujianTA->contains($ujianTA));
        $this->assertInstanceOf(UjianTA::class, $ruang->ujianTA->first());
    }
}
