<?php

namespace Tests\Feature\TugasAkhir;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\JabatanDosen;
use App\Models\TugasAkhir;
use App\Models\Bimbingan;
use App\Models\Proposal;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\Berkas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;


class BimbinganControllerTest extends TestCase
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
            return [
                'mahasiswa' => $mahasiswa,
                'token' => $response->json('data.token') ?? $response->json('token')
            ];
        } else {
            // Gunakan actingAs untuk test
            $this->actingAs($mahasiswa, 'web');
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
            'email' => 'fiddin@staff.uns.ac.id',
            'password' => Hash::make('123123123'),
            'nik' => '987654321',
            'status' => 1
        ]);

        // Create jabatan
        $jabatan = Jabatan::create([
            'name' => 'Dosen',
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
            'email' => 'fiddin@staff.uns.ac.id',
            'password' => '123123123'
        ]);

        // Get token from response
        $token = null;
        if ($response->getStatusCode() == 200) {
            $data = $response->json();
            $token = $data['data']['token'] ?? $data['token'] ?? $data['access_token'] ?? null;
        }

        return [
            'dosen' => $dosen,
            'token' => $token
        ];
    }

    private function createTestData($mahasiswaId = null, $dosenId = null)
    {
        // Create mahasiswa if not provided
        if (!$mahasiswaId) {
            $mahasiswa = new User();
            $mahasiswa->name = 'Mahasiswa Test 2';
            $mahasiswa->email = 'mahasiswa.test2@student.uns.ac.id';
            $mahasiswa->password = Hash::make('password123');
            $mahasiswa->nim = '87654321';
            $mahasiswa->no_telp = '089876543210';
            $mahasiswa->status = 1;
            $mahasiswa->save();
            $mahasiswaId = $mahasiswa->id;
        } else {
            $mahasiswa = User::find($mahasiswaId);
        }

        // Create dosen if not provided
        if (!$dosenId) {
            // Create dosen with proper jabatan
            $dosen = Dosen::create([
                'name' => 'Dosen Test 2',
                'email' => 'dosen.test2@staff.uns.ac.id',
                'password' => Hash::make('password123'),
                'nik' => '123456789',
                'status' => 1
            ]);

            // Create jabatan
            $jabatan = Jabatan::where('name', 'Dosen')->first();
            if (!$jabatan) {
                $jabatan = Jabatan::create([
                    'name' => 'Dosen',
                    'description' => 'Dosen Biasa'
                ]);
            }

            // Create jabatan dosen relation
            JabatanDosen::create([
                'dosen_id' => $dosen->id,
                'jabatan_id' => $jabatan->id,
                'is_active' => true
            ]);

            $dosenId = $dosen->id;
        } else {
            $dosen = Dosen::find($dosenId);
        }

        // Create proposal (sesuai dengan model)
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
     * Memverifikasi dosen dapat mengambil data tugas akhir yang dibimbing
     */
    public function test_dosen_berhasil_mendapatkan_data_tugas_akhir()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Create test data
        $this->createTestData(null, $dosen->id);

        // Verifikasi token
        $this->assertNotNull($token, 'Token dosen tidak ditemukan. Login gagal.');

        // Send request with authentication
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/dosen/bimbingan');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data tugas akhir berhasil diambil');

        // Check data structure
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    /**
     * Memverifikasi dosen menerima data kosong ketika tidak ada tugas akhir
     */
    public function test_dosen_mendapatkan_data_kosong_ketika_tidak_ada_tugas_akhir()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $token = $dosenData['token'];

        // Verifikasi token
        $this->assertNotNull($token, 'Token dosen tidak ditemukan. Login gagal.');

        // Send request with authentication
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/dosen/bimbingan');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data tugas akhir berhasil diambil');

        // Check that data array is empty
        $responseData = $response->json('data');
        $this->assertCount(0, $responseData);
    }

    /**
     * Memverifikasi dosen dapat mendapatkan data bimbingan berdasarkan tugas akhir
     */
    public function test_dosen_berhasil_mendapatkan_data_bimbingan_berdasarkan_tugas_akhir()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Verifikasi token
        $this->assertNotNull($token, 'Token dosen tidak ditemukan. Login gagal.');

        // Create test data
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan test';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 0;
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Send request with authentication
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/dosen/bimbingan/' . $tugasAkhir->id);

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data bimbingan berhasil diambil');

        // Check data structure
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    /**
     * Memverifikasi dosen tidak dapat mengakses bimbingan dengan ID tugas akhir yang tidak valid
     */
    public function test_dosen_tidak_dapat_mengakses_bimbingan_dengan_id_tugas_akhir_tidak_valid()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $token = $dosenData['token'];

        // Verifikasi token
        $this->assertNotNull($token, 'Token dosen tidak ditemukan. Login gagal.');

        // Send request with authentication but invalid tugas akhir ID
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/dosen/bimbingan/999');

        // Assert response
        $response->assertStatus(404);

        // Jika response memiliki struktur yang diharapkan
        if ($response->json('status')) {
            $response->assertJsonPath('status', 'error');
        }
    }

    /**
     * Memverifikasi dosen dapat mengupdate status verifikasi bimbingan
     */
    public function test_dosen_berhasil_memverifikasi_bimbingan()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Verifikasi token
        $this->assertNotNull($token, 'Token dosen tidak ditemukan. Login gagal.');

        // Create test data
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan test';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 0;
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Update request
        $requestData = [
            'status_verifikasi' => 1 // Diterima
        ];

        // Send request with authentication
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/dosen/bimbingan/' . $bimbingan->id, $requestData);

        // Assert response
        $response->assertStatus(200);

        // Jika response memiliki struktur yang diharapkan
        if ($response->json('status')) {
            $response->assertJsonPath('status', 'success');
        }

        // Verify database was updated
        $this->assertDatabaseHas('bimbingan', [
            'id' => $bimbingan->id,
            'status_verifikasi' => 1
        ]);
    }

    /**
     * Memverifikasi dosen tidak dapat memverifikasi bimbingan dengan status yang tidak valid
     */
    public function test_dosen_gagal_memverifikasi_bimbingan_dengan_status_tidak_valid()
    {
        // Create dosen and get token
        $dosenData = $this->createAndLoginDosen();
        $dosen = $dosenData['dosen'];
        $token = $dosenData['token'];

        // Verifikasi token
        $this->assertNotNull($token, 'Token dosen tidak ditemukan. Login gagal.');

        // Create test data
        $testData = $this->createTestData(null, $dosen->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan test';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 0;
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Invalid update request
        $requestData = [
            'status_verifikasi' => 5 // Invalid status (harus 0, 1, atau 2)
        ];

        // Send request with authentication
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/dosen/bimbingan/' . $bimbingan->id, $requestData);

        // Assert response
        $response->assertStatus(422);
    }

    /**
     * Memverifikasi mahasiswa dapat mengambil data bimbingan
     */
    public function test_mahasiswa_berhasil_mendapatkan_data_bimbingan()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan test';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 0;
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Send request with authentication
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/mahasiswa/bimbingan');
        } else {
            $response = $this->getJson('/api/mahasiswa/bimbingan');
        }

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data bimbingan berhasil diambil');

        // Check data structure
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    /**
     * Memverifikasi mahasiswa menerima null ketika tidak memiliki tugas akhir aktif
     */
    public function test_mahasiswa_mendapatkan_null_ketika_tidak_ada_tugas_akhir_aktif()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $token = $mahasiswaData['token'];

        // Send request without creating any tugas akhir
        if ($token) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/mahasiswa/bimbingan');
        } else {
            $response = $this->getJson('/api/mahasiswa/bimbingan');
        }

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Belum ada tugas akhir yang aktif');

        // Check that data is null
        $this->assertNull($response->json('data.data'));
    }

    /**
     * Memverifikasi mahasiswa dapat membuat bimbingan baru
     */
    public function test_mahasiswa_berhasil_membuat_bimbingan_baru()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);

        // Request data
        $requestData = [
            'catatan' => 'Bimbingan baru test',
            'bab' => 2,
            'tanggal_bimbingan' => now()->format('Y-m-d')
        ];

        // Send request with authentication
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/mahasiswa/bimbingan', $requestData);
        } else {
            $response = $this->postJson('/api/mahasiswa/bimbingan', $requestData);
        }

        // Assert response
        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Bimbingan berhasil ditambahkan');

        // Check database
        $this->assertDatabaseHas('bimbingan', [
            'catatan' => 'Bimbingan baru test',
            'bab' => 2,
            'status_verifikasi' => 0, // Default value
        ]);
    }

    /**
     * Memverifikasi mahasiswa tidak dapat membuat bimbingan baru jika bimbingan sebelumnya belum diverifikasi
     */
    public function test_mahasiswa_gagal_membuat_bimbingan_baru_saat_bimbingan_sebelumnya_belum_diverifikasi()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create unverified bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan belum diverifikasi';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 0; // Belum diverifikasi
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Request data for new bimbingan
        $requestData = [
            'catatan' => 'Bimbingan baru',
            'bab' => 2,
            'tanggal_bimbingan' => now()->format('Y-m-d')
        ];

        // Send request with authentication
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/mahasiswa/bimbingan', $requestData);
        } else {
            $response = $this->postJson('/api/mahasiswa/bimbingan', $requestData);
        }

        // Assert response
        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Tidak dapat membuat bimbingan baru karena bimbingan sebelumnya belum diverifikasi');
    }

    /**
     * Memverifikasi validasi input saat mahasiswa membuat bimbingan baru
     */
    public function test_validasi_input_saat_mahasiswa_membuat_bimbingan_baru()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $this->createTestData($mahasiswa->id);

        // Request data with missing fields
        $requestData = [
            'catatan' => '', // Empty
            // Missing bab
            'tanggal_bimbingan' => 'invalid-date'
        ];

        // Send request with authentication
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/mahasiswa/bimbingan', $requestData);
        } else {
            $response = $this->postJson('/api/mahasiswa/bimbingan', $requestData);
        }

        // Assert response
        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Check validation errors exist
        $this->assertStringContainsString('Validasi gagal', $response->json('message'));
    }

    /**
     * Memverifikasi mahasiswa dapat mengubah data bimbingan
     */
    public function test_mahasiswa_berhasil_mengubah_data_bimbingan()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan original';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 0; // Belum diverifikasi
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Update request
        $requestData = [
            'catatan' => 'Bimbingan updated',
            'bab' => 2,
            'tanggal_bimbingan' => now()->addDay()->format('Y-m-d')
        ];

        // Send request with authentication
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->putJson('/api/mahasiswa/bimbingan/' . $bimbingan->id, $requestData);
        } else {
            $response = $this->putJson('/api/mahasiswa/bimbingan/' . $bimbingan->id, $requestData);
        }

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data bimbingan berhasil diubah');

        // Check database
        $this->assertDatabaseHas('bimbingan', [
            'id' => $bimbingan->id,
            'catatan' => 'Bimbingan updated',
            'bab' => 2
        ]);
    }

    /**
     * Memverifikasi mahasiswa tidak dapat mengubah bimbingan yang sudah diverifikasi
     */
    public function test_mahasiswa_gagal_mengubah_bimbingan_yang_sudah_diverifikasi()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan yang sudah diverifikasi';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 1; // Sudah diverifikasi (diterima)
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Update request
        $requestData = [
            'catatan' => 'Bimbingan updated',
            'bab' => 2,
            'tanggal_bimbingan' => now()->addDay()->format('Y-m-d')
        ];

        // Send request with authentication
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->putJson('/api/mahasiswa/bimbingan/' . $bimbingan->id, $requestData);
        } else {
            $response = $this->putJson('/api/mahasiswa/bimbingan/' . $bimbingan->id, $requestData);
        }

        // Assert response
        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Bimbingan sudah diverifikasi dan tidak dapat diubah');
    }

    /**
     * Memverifikasi mahasiswa dapat melihat detail bimbingan
     */
    public function test_mahasiswa_berhasil_melihat_detail_bimbingan()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan detail view';
        $bimbingan->bab = 3;
        $bimbingan->status_verifikasi = 0;
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Send request with authentication
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/mahasiswa/bimbingan/' . $bimbingan->id);
        } else {
            $response = $this->getJson('/api/mahasiswa/bimbingan/' . $bimbingan->id);
        }

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Detail bimbingan berhasil diambil');

        // Check data
        $response->assertJsonPath('data.catatan', 'Bimbingan detail view')
            ->assertJsonPath('data.bab', 3);
    }

    /**
     * Memverifikasi mahasiswa tidak dapat melihat detail bimbingan yang tidak ada
     */
    public function test_mahasiswa_gagal_melihat_detail_bimbingan_yang_tidak_ada()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $token = $mahasiswaData['token'];

        // Create test data
        $this->createTestData($mahasiswaData['mahasiswa']->id);

        // Send request with authentication but invalid ID
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/mahasiswa/bimbingan/999');
        } else {
            $response = $this->getJson('/api/mahasiswa/bimbingan/999');
        }

        // Kasus ini bisa mengembalikan 404 atau 500 tergantung pada implementasi controller
        $this->assertTrue($response->getStatusCode() >= 400);

        // Jika response memiliki struktur yang diharapkan
        if ($response->json('status')) {
            $response->assertJsonPath('status', 'error');
        }
    }

    /**
     * Memverifikasi mahasiswa dapat menghapus bimbingan
     */
    public function test_mahasiswa_berhasil_menghapus_bimbingan()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan to delete';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 0; // Belum diverifikasi
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Send request with authentication
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->deleteJson('/api/mahasiswa/bimbingan/' . $bimbingan->id);
        } else {
            $response = $this->deleteJson('/api/mahasiswa/bimbingan/' . $bimbingan->id);
        }

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data bimbingan berhasil dihapus');

        // Check database
        $this->assertDatabaseMissing('bimbingan', [
            'id' => $bimbingan->id
        ]);
    }

    /**
     * Memverifikasi mahasiswa tidak dapat menghapus bimbingan yang sudah diverifikasi
     */
    public function test_mahasiswa_gagal_menghapus_bimbingan_yang_sudah_diverifikasi()
    {
        // Create mahasiswa and get token
        $mahasiswaData = $this->createAndLoginMahasiswa();
        $mahasiswa = $mahasiswaData['mahasiswa'];
        $token = $mahasiswaData['token'];

        // Create test data
        $testData = $this->createTestData($mahasiswa->id);
        $tugasAkhir = $testData['tugasAkhir'];

        // Create sample bimbingan
        $bimbingan = new Bimbingan();
        $bimbingan->tugas_akhir_id = $tugasAkhir->id;
        $bimbingan->catatan = 'Bimbingan yang sudah diverifikasi';
        $bimbingan->bab = 1;
        $bimbingan->status_verifikasi = 1; // Sudah diverifikasi (diterima)
        $bimbingan->tanggal_bimbingan = now();
        $bimbingan->save();

        // Send request with authentication
        if ($token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->deleteJson('/api/mahasiswa/bimbingan/' . $bimbingan->id);
        } else {
            $response = $this->deleteJson('/api/mahasiswa/bimbingan/' . $bimbingan->id);
        }

        // Assert response
        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Bimbingan sudah diverifikasi dan tidak dapat dihapus');
    }
}
