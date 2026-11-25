<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\Berkas;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class BerkasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_berkas()
    {
        // Test pembuatan instance berkas
        $berkas = Berkas::factory()->create();

        $this->assertInstanceOf(Berkas::class, $berkas);
        $this->assertDatabaseHas('berkas', ['id' => $berkas->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $berkas = new Berkas([
            'berkasable_type' => Proposal::class,
            'berkasable_id' => 1,
            'nama_berkas' => 'Dokumen Proposal',
            'file_path' => 'storage/proposal/dokumen_1.pdf',
            'status_valid' => true,
        ]);

        $this->assertEquals(Proposal::class, $berkas->berkasable_type);
        $this->assertEquals(1, $berkas->berkasable_id);
        $this->assertEquals('Dokumen Proposal', $berkas->nama_berkas);
        $this->assertEquals('storage/proposal/dokumen_1.pdf', $berkas->file_path);
        $this->assertEquals(true, $berkas->status_valid);
    }
    /** @test */
    public function memiliki_relasi_polymorphic()
    {        // Test relasi polymorphic
        $proposal = Proposal::factory()->create();

        // Gunakan method forBerkasable untuk mengatur relasi dengan benar
        $berkas = Berkas::factory()
            ->forBerkasable(Proposal::class, $proposal->id)
            ->create()
            ->fresh();

        // Debug: Cek apakah data di database sesuai dengan yang diharapkan
        $berkasData = DB::table('berkas')->where('id', $berkas->id)->first();
        $this->assertEquals(Proposal::class, $berkasData->berkasable_type);
        $this->assertEquals($proposal->id, $berkasData->berkasable_id);

        // Cek dengan cara lain menggunakan DB query
        $relatedProposal = Proposal::find($berkasData->berkasable_id);
        $this->assertNotNull($relatedProposal);
        $this->assertEquals($proposal->id, $relatedProposal->id);
    }
}
