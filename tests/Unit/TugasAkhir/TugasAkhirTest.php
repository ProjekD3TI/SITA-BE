<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\TugasAkhir;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Proposal;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\Kategori;
use App\Models\NilaiBimbingan;
use App\Models\Bimbingan;
use App\Models\UjianTA;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TugasAkhirTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_tugas_akhir()
    {
        // Test pembuatan instance tugas akhir
        $tugasAkhir = TugasAkhir::factory()->create();

        $this->assertInstanceOf(TugasAkhir::class, $tugasAkhir);
        $this->assertDatabaseHas('tugas_akhir', ['id' => $tugasAkhir->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $tugasAkhir = new TugasAkhir([
            'mahasiswa_id' => 1,
            'proposal_id' => 1,
            'semester_id' => 1,
            'dosen_pembimbing_id' => 1,
            'tahun_ajaran_id' => 1,
            'nilai_bimbingan_id' => 1,
            'kategori_id' => 1,
            'judul_tugas_akhir' => 'Sistem Informasi Manajemen Tugas Akhir',
            'catatan_proposal' => 'Proposal disetujui dengan revisi minor',
            'nilai_proposal' => 85,
            'nilai_akhir' => 87.5,
            'status_valid' => true,
            'status_aktif' => true
        ]);

        $this->assertEquals(1, $tugasAkhir->mahasiswa_id);
        $this->assertEquals(1, $tugasAkhir->proposal_id);
        $this->assertEquals(1, $tugasAkhir->semester_id);
        $this->assertEquals(1, $tugasAkhir->dosen_pembimbing_id);
        $this->assertEquals(1, $tugasAkhir->tahun_ajaran_id);
        $this->assertEquals(1, $tugasAkhir->nilai_bimbingan_id);
        $this->assertEquals(1, $tugasAkhir->kategori_id);
        $this->assertEquals('Sistem Informasi Manajemen Tugas Akhir', $tugasAkhir->judul_tugas_akhir);
        $this->assertEquals('Proposal disetujui dengan revisi minor', $tugasAkhir->catatan_proposal);
        $this->assertEquals(85, $tugasAkhir->nilai_proposal);
        $this->assertEquals(87.5, $tugasAkhir->nilai_akhir);
        $this->assertEquals(true, $tugasAkhir->status_valid);
        $this->assertEquals(true, $tugasAkhir->status_aktif);
    }

    /** @test */
    public function menggunakan_table_tugas_akhir()
    {
        // Test model menggunakan table yang benar
        $tugasAkhir = new TugasAkhir();

        $this->assertEquals('tugas_akhir', $tugasAkhir->getTable());
    }

    /** @test */
    public function memiliki_relasi_mahasiswa()
    {
        // Test relasi dengan User (Mahasiswa)
        $mahasiswa = User::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'mahasiswa_id' => $mahasiswa->id
        ]);

        $this->assertInstanceOf(User::class, $tugasAkhir->mahasiswa);
        $this->assertEquals($mahasiswa->id, $tugasAkhir->mahasiswa->id);
    }

    /** @test */
    public function memiliki_relasi_dosen_pembimbing()
    {
        // Test relasi dengan Dosen
        $dosen = Dosen::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'dosen_pembimbing_id' => $dosen->id
        ]);

        $this->assertInstanceOf(Dosen::class, $tugasAkhir->dosenPembimbing);
        $this->assertEquals($dosen->id, $tugasAkhir->dosenPembimbing->id);
    }

    /** @test */
    public function memiliki_relasi_proposal()
    {
        // Test relasi dengan Proposal
        $proposal = Proposal::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'proposal_id' => $proposal->id
        ]);

        $this->assertInstanceOf(Proposal::class, $tugasAkhir->proposal);
        $this->assertEquals($proposal->id, $tugasAkhir->proposal->id);
    }

    /** @test */
    public function memiliki_relasi_bimbingan()
    {
        // Test relasi dengan Bimbingan
        $tugasAkhir = TugasAkhir::factory()->create();
        $bimbingan = Bimbingan::factory()->create([
            'tugas_akhir_id' => $tugasAkhir->id
        ]);

        $this->assertTrue($tugasAkhir->bimbingan->contains($bimbingan));
        $this->assertInstanceOf(Bimbingan::class, $tugasAkhir->bimbingan->first());
    }

    /** @test */
    public function memiliki_relasi_semester()
    {
        // Test relasi dengan Semester
        $semester = Semester::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'semester_id' => $semester->id
        ]);

        $this->assertInstanceOf(Semester::class, $tugasAkhir->semester);
        $this->assertEquals($semester->id, $tugasAkhir->semester->id);
    }

    /** @test */
    public function memiliki_relasi_tahun_ajaran()
    {
        // Test relasi dengan TahunAjaran
        $tahunAjaran = TahunAjaran::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'tahun_ajaran_id' => $tahunAjaran->id
        ]);

        $this->assertInstanceOf(TahunAjaran::class, $tugasAkhir->tahunAjaran);
        $this->assertEquals($tahunAjaran->id, $tugasAkhir->tahunAjaran->id);
    }

    /** @test */
    public function memiliki_relasi_ujian_ta()
    {
        // Test relasi dengan UjianTA
        $tugasAkhir = TugasAkhir::factory()->create();
        $ujianTA = UjianTA::factory()->create([
            'tugas_akhir_id' => $tugasAkhir->id
        ]);

        $this->assertTrue($tugasAkhir->ujianTA->contains($ujianTA));
        $this->assertInstanceOf(UjianTA::class, $tugasAkhir->ujianTA->first());
    }

    /** @test */
    public function memiliki_relasi_nilai_bimbingan()
    {
        // Test relasi dengan NilaiBimbingan
        $nilaiBimbingan = NilaiBimbingan::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'nilai_bimbingan_id' => $nilaiBimbingan->id
        ]);

        $this->assertInstanceOf(NilaiBimbingan::class, $tugasAkhir->nilaiBimbingan);
        $this->assertEquals($nilaiBimbingan->id, $tugasAkhir->nilaiBimbingan->id);
    }

    /** @test */
    public function memiliki_relasi_kategori()
    {
        // Test relasi dengan Kategori
        $kategori = Kategori::factory()->create();
        $tugasAkhir = TugasAkhir::factory()->create([
            'kategori_id' => $kategori->id
        ]);

        $this->assertInstanceOf(Kategori::class, $tugasAkhir->kategori);
        $this->assertEquals($kategori->id, $tugasAkhir->kategori->id);
    }
}
