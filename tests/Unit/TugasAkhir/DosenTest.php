<?php

namespace Tests\Unit;

use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\RequestPembimbing;
use App\Models\TugasAkhir;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class DosenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_dosen()
    {
        $dosen = Dosen::factory()->create();

        $this->assertInstanceOf(Dosen::class, $dosen);
        $this->assertDatabaseHas('dosen', ['id' => $dosen->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        $dosen = new Dosen([
            'name' => 'Prof. Andi',
            'email' => 'andi@staff.uns.ac.id',
            'password' => bcrypt('password'),
            'nik' => '1234567890',
            'status' => 1,
        ]);

        $this->assertEquals('Prof. Andi', $dosen->name);
        $this->assertEquals('andi@staff.uns.ac.id', $dosen->email);
        $this->assertEquals('1234567890', $dosen->nik);
        $this->assertEquals(1, $dosen->status);
    }

    /** @test */
    public function menyembunyikan_atribut_password()
    {
        $dosen = Dosen::factory()->create([
            'password' => bcrypt('password')
        ]);

        $data = $dosen->toArray();
        $this->assertArrayNotHasKey('password', $data);
    }

    /** @test */
    public function mengubah_password_menjadi_hashed()
    {
        $dosen = new Dosen();
        $dosen->password = 'plain-text-password';

        $this->assertTrue(Hash::check('plain-text-password', $dosen->password));
    }

    /** @test */
    public function memiliki_banyak_jabatan()
    {
        $dosen = Dosen::factory()->has(Jabatan::factory())->create();

        $this->assertInstanceOf(Jabatan::class, $dosen->jabatans->first());
        $this->assertNotNull($dosen->jabatans);
    }

    /** @test */
    public function memiliki_banyak_request_pembimbing()
    {
        $dosen = Dosen::factory()->create();

        // Perbaikan: Pastikan ProposalFactory menggunakan nilai integer untuk status_proposal
        $proposal = Proposal::factory()->create([
            'status_proposal' => 1, // Gunakan nilai integer untuk status proposal
        ]);

        $tugasAkhir = TugasAkhir::factory()->create([
            'proposal_id' => $proposal->id
        ]);

        $request = RequestPembimbing::factory()->create([
            'dosen_id' => $dosen->id,
            'tugas_akhir_id' => $tugasAkhir->id
        ]);

        $this->assertInstanceOf(RequestPembimbing::class, $dosen->requestPembimbing->first());
        $this->assertEquals($request->id, $dosen->requestPembimbing->first()->id);
    }

    /** @test */
    public function memiliki_banyak_tugas_akhir()
    {
        $dosen = Dosen::factory()->create();
        $proposal = Proposal::factory()->create(['status_proposal' => 1]);

        $tugasAkhir = TugasAkhir::factory()->create([
            'proposal_id' => $proposal->id
        ]);

        // Tambahkan dosen sebagai penguji
        $tugasAkhir->dosenPenguji()->attach($dosen->id, [
            'revisi' => 'Revisi bab 3',
            'catatan_semhas' => 'Perlu perbaikan metodologi'
        ]);

        // Refresh model untuk memuat relasi terbaru
        $dosen = $dosen->fresh();

        // Gunakan relasi tugasAkhir yang sudah ada
        $this->assertInstanceOf(TugasAkhir::class, $dosen->tugasAkhir->first());
    }
}
