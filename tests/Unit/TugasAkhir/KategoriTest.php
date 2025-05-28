<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Kategori;
use App\Models\TugasAkhir;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_kategori()
    {
        // Test pembuatan instance kategori
        $kategori = Kategori::factory()->create();

        $this->assertInstanceOf(Kategori::class, $kategori);
        $this->assertDatabaseHas('kategori', ['id' => $kategori->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $kategori = new Kategori([
            'nama_kategori' => 'Web Development',
        ]);

        $this->assertEquals('Web Development', $kategori->nama_kategori);
    }

    /** @test */
    public function menggunakan_table_kategori()
    {
        // Test model menggunakan table yang benar
        $kategori = new Kategori();

        $this->assertEquals('kategori', $kategori->getTable());
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $kategori = Kategori::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'kategori_id' => $kategori->id
        ]);

        $this->assertTrue($kategori->tugasAkhir->contains($tugasAkhir));
        $this->assertInstanceOf(TugasAkhir::class, $kategori->tugasAkhir->first());
    }
}
