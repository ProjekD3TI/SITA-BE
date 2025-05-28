<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\RequestPembimbing;
use App\Models\TugasAkhir;
use App\Models\Dosen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestPembimbingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_request_pembimbing()
    {
        // Test pembuatan instance request pembimbing
        $requestPembimbing = RequestPembimbing::factory()->create();

        $this->assertInstanceOf(RequestPembimbing::class, $requestPembimbing);
        $this->assertDatabaseHas('request_pembimbing', ['id' => $requestPembimbing->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $tanggalRequest = now();
        $tanggalRespon = now()->addDays(2);

        $requestPembimbing = new RequestPembimbing([
            'tugas_akhir_id' => 1,
            'dosen_id' => 1,
            'status_request' => 'disetujui',
            'tanggal_request' => $tanggalRequest,
            'tanggal_respon' => $tanggalRespon,
        ]);

        $this->assertEquals(1, $requestPembimbing->tugas_akhir_id);
        $this->assertEquals(1, $requestPembimbing->dosen_id);
        $this->assertEquals('disetujui', $requestPembimbing->status_request);
        $this->assertEquals($tanggalRequest, $requestPembimbing->tanggal_request);
        $this->assertEquals($tanggalRespon, $requestPembimbing->tanggal_respon);
    }

    /** @test */
    public function menggunakan_table_request_pembimbing()
    {
        // Test model menggunakan table yang benar
        $requestPembimbing = new RequestPembimbing();

        $this->assertEquals('request_pembimbing', $requestPembimbing->getTable());
    }

    /** @test */
    public function memiliki_relasi_tugas_akhir()
    {
        // Test relasi dengan TugasAkhir
        $tugasAkhir = TugasAkhir::factory()->create();
        $requestPembimbing = RequestPembimbing::factory()->create([
            'tugas_akhir_id' => $tugasAkhir->id
        ]);

        $this->assertInstanceOf(TugasAkhir::class, $requestPembimbing->tugasAkhir);
        $this->assertEquals($tugasAkhir->id, $requestPembimbing->tugasAkhir->id);
    }

    /** @test */
    public function memiliki_relasi_dosen()
    {
        // Test relasi dengan Dosen
        $dosen = Dosen::factory()->create();
        $requestPembimbing = RequestPembimbing::factory()->create([
            'dosen_id' => $dosen->id
        ]);

        $this->assertInstanceOf(Dosen::class, $requestPembimbing->dosen);
        $this->assertEquals($dosen->id, $requestPembimbing->dosen->id);
    }
}
