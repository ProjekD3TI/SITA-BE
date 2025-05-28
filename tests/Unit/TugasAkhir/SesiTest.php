<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Sesi;
use App\Models\UjianTA;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SesiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_sesi()
    {
        // Test pembuatan instance sesi
        $sesi = Sesi::factory()->create();

        $this->assertInstanceOf(Sesi::class, $sesi);
        $this->assertDatabaseHas('sesi', ['id' => $sesi->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $sesi = new Sesi([
            'nama_sesi' => 'Sesi Pagi',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00',
        ]);

        $this->assertEquals('Sesi Pagi', $sesi->nama_sesi);
        $this->assertEquals('08:00:00', $sesi->waktu_mulai);
        $this->assertEquals('10:00:00', $sesi->waktu_selesai);
    }

    /** @test */
    public function menggunakan_table_sesi()
    {
        // Test model menggunakan table yang benar
        $sesi = new Sesi();

        $this->assertEquals('sesi', $sesi->getTable());
    }

    /** @test */
    public function memiliki_relasi_ujian_ta()
    {
        // Test relasi dengan UjianTA
        $sesi = Sesi::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'sesi_id' => $sesi->id
        ]);

        $this->assertTrue($sesi->ujianTA->contains($ujianTA));
        $this->assertInstanceOf(UjianTA::class, $sesi->ujianTA->first());
    }
}
