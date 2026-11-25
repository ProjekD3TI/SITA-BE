<?php

namespace Tests\Feature\TugasAkhir;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\JabatanDosen;
use App\Models\TugasAkhir;
use App\Models\Proposal;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\NilaiBimbingan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NilaiBimbinganControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createAndLoginMahasiswa()
    {
        $mahasiswa = new User();
        $mahasiswa->name = 'veri';
        $mahasiswa->email = 'veri@student.uns.ac.id';
        $mahasiswa->password = Hash::make('123123123');
        $mahasiswa->nim = 'v342300';
        $mahasiswa->no_telp = '081234567890';
        $mahasiswa->status = 1;
        $mahasiswa->save();

        $response = $this->postJson('/api/login/mahasiswa', [
            'email' => 'veri@student.uns.ac.id',
            'password' => '123123123'
        ]);

        // Periksa jika login berhasil, gunakan actingAs sebagai fallback
        if ($response->getStatusCode() == 200) {
            $token = $response->json('token');
            return [
                'mahasiswa' => $mahasiswa,
                'token' => $token
            ];
        } else {
            $this->actingAs($mahasiswa);
            return [
                'mahasiswa' => $mahasiswa,
                'token' => null
            ];
        }
    }

    private function createAndLoginDosen()
    {
        $dosen = Dosen::create([
            'name' => 'Dosen Test',
            'email' => 'dosen@staff.uns.ac.id',
            'password' => Hash::make('123123123'),
            'nik' => '1234567890',
            'status' => 1
        ]);

        // Create jabatan
        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Dosen Biasa',
            'name' => 'Dosen Biasa',
            'description' => 'Dosen Biasa'
        ]);

        // Create jabatan dosen relation
        JabatanDosen::create([
            'dosen_id' => $dosen->id,
            'jabatan_id' => $jabatan->id,
            'is_active' => true
        ]);

        // Login using postJson like in DosenAuthTest
        $response = $this->postJson('/api/login/dosen', [
            'email' => 'dosen@staff.uns.ac.id',
            'password' => '123123123'
        ]);

        // Get token from response
        $token = null;
        if ($response->getStatusCode() == 200) {
            $token = $response->json('token');
        } else {
            $this->actingAs($dosen);
        }

        return [
            'dosen' => $dosen,
            'user' => $dosen,
            'token' => $token
        ];
    }

    private function createTestData($mahasiswaId = null, $dosenId = null)
    {
        if (!$mahasiswaId) {
            $mahasiswa = User::create([
                'name' => 'Test Mahasiswa',
                'email' => 'test@student.uns.ac.id',
                'password' => Hash::make('123123123'),
                'nim' => 'm123456',
                'no_telp' => '081234567892',
                'status' => 1
            ]);
            $mahasiswaId = $mahasiswa->id;
        } else {
            $mahasiswa = User::find($mahasiswaId);
        }

        if (!$dosenId) {
            $dosen = Dosen::create([
                'name' => 'Test Dosen',
                'email' => 'testdosen@staff.uns.ac.id',
                'password' => Hash::make('123123123'),
                'nik' => '0987654321', // Ganti nip menjadi nik
                'status' => 1
            ]);
            $dosenId = $dosen->id;
        } else {
            $dosen = Dosen::find($dosenId);
        }

        // Create proposal
        $proposal = new Proposal();
        $proposal->judul_proposal = 'Proposal Test';
        $proposal->status_proposal = 1;
        $proposal->tanggal_unggah = now();
        $proposal->save();

        // Create semester
        $semester = new Semester();
        $semester->nama_semester = 'Ganjil';
        $semester->save();

        // Create tahun ajaran
        $tahunAjaran = new TahunAjaran();
        $tahunAjaran->tahun_ajaran = '2023/2024';
        $tahunAjaran->save();

        // Create tugas akhir
        $tugasAkhir = new TugasAkhir();
        $tugasAkhir->judul_tugas_akhir = 'Judul Tugas Akhir Test';
        $tugasAkhir->mahasiswa_id = $mahasiswaId;
        $tugasAkhir->dosen_pembimbing_id = $dosenId;
        $tugasAkhir->proposal_id = $proposal->id;
        $tugasAkhir->semester_id = $semester->id;
        $tugasAkhir->tahun_ajaran_id = $tahunAjaran->id;
        $tugasAkhir->status_aktif = 1;
        $tugasAkhir->save();

        return [
            'mahasiswa' => $mahasiswa,
            'dosen' => $dosen,
            'proposal' => $proposal,
            'semester' => $semester,
            'tahunAjaran' => $tahunAjaran,
            'tugasAkhir' => $tugasAkhir
        ];
    }

    /**
     * Memverifikasi mahasiswa dapat mengambil data nilai bimbingan mereka
     */
    public function test_mahasiswa_dapat_mengambil_data_nilai_bimbingan()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create nilai bimbingan
        $nilaiBimbingan = NilaiBimbingan::create([
            'rerata_bimbingan' => 90,
            'disiplin' => 85,
            'komunikasi' => 80,
            'skill_pengetahuan' => 95,
            'tingkah_laku' => 90,
        ]);

        // Connect nilai bimbingan to tugas akhir
        $tugasAkhir->update(['nilai_bimbingan_id' => $nilaiBimbingan->id]);

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/mahasiswa/nilai-bimbingan');
        } else {
            $response = $this->actingAs($mahasiswa)
                ->getJson('/api/mahasiswa/nilai-bimbingan');
        }

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'rerata_bimbingan',
                'disiplin',
                'komunikasi',
                'skill_pengetahuan',
                'tingkah_laku',
            ],
            'message'
        ]);
    }

    /**
     * Memverifikasi mahasiswa menerima error jika belum memiliki nilai bimbingan
     */
    public function test_mahasiswa_menerima_error_jika_belum_memiliki_nilai_bimbingan()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data (tugas akhir without nilai bimbingan)
        $this->createTestData($mahasiswa->id);

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/mahasiswa/nilai-bimbingan');
        } else {
            $response = $this->actingAs($mahasiswa)
                ->getJson('/api/mahasiswa/nilai-bimbingan');
        }

        // Assert response
        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Memverifikasi mahasiswa menerima error jika tidak memiliki tugas akhir aktif
     */
    public function test_mahasiswa_menerima_error_jika_tidak_memiliki_tugas_akhir_aktif()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/mahasiswa/nilai-bimbingan');
        } else {
            $response = $this->actingAs($mahasiswa)
                ->getJson('/api/mahasiswa/nilai-bimbingan');
        }

        // Assert response
        $response->assertStatus(500);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Memverifikasi dosen dapat mengambil data nilai bimbingan berdasarkan tugas akhir
     */
    public function test_dosen_dapat_mengambil_data_nilai_bimbingan()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Create test data
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create nilai bimbingan
        $nilaiBimbingan = NilaiBimbingan::create([
            'rerata_bimbingan' => 90,
            'disiplin' => 85,
            'komunikasi' => 80,
            'skill_pengetahuan' => 95,
            'tingkah_laku' => 90,
        ]);

        // Connect nilai bimbingan to tugas akhir
        $tugasAkhir->update(['nilai_bimbingan_id' => $nilaiBimbingan->id]);

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id);
        } else {
            $response = $this->actingAs($dosenData['user'])
                ->getJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id);
        }

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'rerata_bimbingan',
                'disiplin',
                'komunikasi',
                'skill_pengetahuan',
                'tingkah_laku',
            ],
            'message'
        ]);
    }

    /**
     * Memverifikasi dosen menerima error ketika mengakses tugas akhir yang tidak ada
     */
    public function test_dosen_menerima_error_saat_mengakses_tugas_akhir_tidak_ada()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $token = $dosenData['token'];

        // Send request with authentication but non-existent tugas akhir ID
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/dosen/nilai-bimbingan/999');
        } else {
            $response = $this->actingAs($dosenData['user'])
                ->getJson('/api/dosen/nilai-bimbingan/999');
        }

        // Assert response
        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Memverifikasi dosen menerima error saat mengakses tugas akhir yang bukan dibimbingnya
     */
    public function test_dosen_menerima_error_saat_mengakses_tugas_akhir_bukan_pembimbing()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $token = $dosenData['token'];

        // Create test data with different dosen
        $testData = $this->createTestData();
        $tugasAkhir = $testData['tugasAkhir'];

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id);
        } else {
            $response = $this->actingAs($dosenData['user'])
                ->getJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id);
        }

        // Assert response
        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Memverifikasi dosen berhasil memberikan nilai bimbingan
     */
    public function test_dosen_berhasil_memberikan_nilai_bimbingan()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Create test data
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Nilai bimbingan data
        $nilaiBimbinganData = [
            'rerata_bimbingan' => 90,
            'disiplin' => 85,
            'komunikasi' => 80,
            'skill_pengetahuan' => 95,
            'tingkah_laku' => 90,
        ];

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->postJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id, $nilaiBimbinganData);
        } else {
            $response = $this->actingAs($dosenData['user'])
                ->postJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id, $nilaiBimbinganData);
        }

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'rerata_bimbingan',
                'disiplin',
                'komunikasi',
                'skill_pengetahuan',
                'tingkah_laku',
            ],
            'message'
        ]);

        // Verify data was stored correctly
        $this->assertDatabaseHas('nilai_bimbingan', [
            'rerata_bimbingan' => 90,
            'disiplin' => 85,
            'komunikasi' => 80,
            'skill_pengetahuan' => 95,
            'tingkah_laku' => 90,
        ]);
    }

    /**
     * Memverifikasi validasi input saat dosen memberikan nilai bimbingan
     */
    public function test_validasi_input_saat_dosen_memberikan_nilai_bimbingan()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Create test data
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Invalid nilai bimbingan data (missing required fields)
        $nilaiBimbinganData = [
            'rerata_bimbingan' => 90,
            // Missing other required fields
        ];

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->postJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id, $nilaiBimbinganData);
        } else {
            $response = $this->actingAs($dosenData['user'])
                ->postJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id, $nilaiBimbinganData);
        }

        // Assert response
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Memverifikasi dosen gagal memberikan nilai bimbingan untuk tugas akhir yang sudah memiliki nilai
     */
    public function test_dosen_gagal_memberikan_nilai_bimbingan_untuk_tugas_akhir_yang_sudah_memiliki_nilai()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Create test data
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create nilai bimbingan
        $nilaiBimbingan = NilaiBimbingan::create([
            'rerata_bimbingan' => 90,
            'disiplin' => 85,
            'komunikasi' => 80,
            'skill_pengetahuan' => 95,
            'tingkah_laku' => 90,
        ]);

        // Connect nilai bimbingan to tugas akhir
        $tugasAkhir->update(['nilai_bimbingan_id' => $nilaiBimbingan->id]);

        // Nilai bimbingan data for update attempt
        $nilaiBimbinganData = [
            'rerata_bimbingan' => 95,
            'disiplin' => 90,
            'komunikasi' => 85,
            'skill_pengetahuan' => 90,
            'tingkah_laku' => 95,

        ];

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->postJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id, $nilaiBimbinganData);
        } else {
            $response = $this->actingAs($dosenData['user'])
                ->postJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id, $nilaiBimbinganData);
        }

        // Assert response
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Memverifikasi dosen berhasil mengupdate nilai bimbingan
     */
    public function test_dosen_berhasil_mengupdate_nilai_bimbingan()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Create test data
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create nilai bimbingan
        $nilaiBimbingan = NilaiBimbingan::create([
            'rerata_bimbingan' => 90,
            'disiplin' => 85,
            'komunikasi' => 80,
            'skill_pengetahuan' => 95,
            'tingkah_laku' => 90,
        ]);

        // Connect nilai bimbingan to tugas akhir
        $tugasAkhir->update(['nilai_bimbingan_id' => $nilaiBimbingan->id]);

        // Updated nilai bimbingan data
        $updatedData = [
            'rerata_bimbingan' => 95,
            'disiplin' => 90,
            'komunikasi' => 85,
            'skill_pengetahuan' => 90,
            'tingkah_laku' => 95,

        ];

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->putJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id . '/update', $updatedData);
        } else {
            $response = $this->actingAs($dosenData['user'])
                ->putJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id . '/update', $updatedData);
        }

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'rerata_bimbingan',
                'disiplin',
                'komunikasi',
                'skill_pengetahuan',
                'tingkah_laku',
            ],
            'message'
        ]);

        // Verify data was updated correctly
        $this->assertDatabaseHas('nilai_bimbingan', [
            'rerata_bimbingan' => 95,
            'disiplin' => 90,
            'komunikasi' => 85,
            'skill_pengetahuan' => 90,
            'tingkah_laku' => 95,

        ]);
    }

    /**
     * Memverifikasi dosen gagal mengupdate nilai bimbingan yang tidak ada
     */
    public function test_dosen_gagal_mengupdate_nilai_bimbingan_yang_tidak_ada()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Create test data without nilai bimbingan
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Updated nilai bimbingan data
        $updatedData = [
            'rerata_bimbingan' => 95,
            'disiplin' => 90,
            'komunikasi' => 85,
            'skill_pengetahuan' => 90,
            'tingkah_laku' => 95,
        ];

        // Send request with authentication
        $response = null;
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->putJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id . '/update', $updatedData);
        } else {
            $response = $this->actingAs($dosenData['user'])
                ->putJson('/api/dosen/nilai-bimbingan/' . $tugasAkhir->id . '/update', $updatedData);
        }

        // Assert response
        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }
}
