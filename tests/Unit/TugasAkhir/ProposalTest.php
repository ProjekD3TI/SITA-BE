<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Proposal;
use App\Models\TugasAkhir;
use App\Models\Berkas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_proposal()
    {
        // Test pembuatan instance proposal
        $proposal = Proposal::factory()->create();

        $this->assertInstanceOf(Proposal::class, $proposal);
        $this->assertDatabaseHas('proposal', ['id' => $proposal->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $proposal = new Proposal([
            'judul_proposal' => 'Pengembangan Sistem Informasi Tugas Akhir',
            'status_proposal' => 'disetujui',
            'tanggal_unggah' => now(),
        ]);

        $this->assertEquals('Pengembangan Sistem Informasi Tugas Akhir', $proposal->judul_proposal);
        $this->assertEquals('disetujui', $proposal->status_proposal);
        $this->assertNotNull($proposal->tanggal_unggah);
    }

    /** @test */
    public function menggunakan_table_proposal()
    {
        // Test model menggunakan table yang benar
        $proposal = new Proposal();

        $this->assertEquals('proposal', $proposal->getTable());
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $proposal = Proposal::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'proposal_id' => $proposal->id
        ]);

        $this->assertInstanceOf(TugasAkhir::class, $proposal->tugasAkhir);
        $this->assertEquals($tugasAkhir->id, $proposal->tugasAkhir->id);
    }

    /** @test */
    public function memiliki_relasi_berkas()
    {
        // Test relasi dengan Berkas (morphMany)
        $proposal = Proposal::factory()->create();
        $berkas = Berkas::factory()->create([
            'berkasable_id' => $proposal->id,
            'berkasable_type' => Proposal::class
        ]);

        $this->assertTrue($proposal->berkas->contains($berkas));
        $this->assertInstanceOf(Berkas::class, $proposal->berkas->first());
    }
}
