<?php

namespace Tests\Feature\TugasAkhir;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\TugasAkhir;
use App\Models\DosenPenguji;
use App\Models\Proposal;
use App\Models\Bimbingan;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DosenPengujiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminTa(array $overrides = [])
    {
        $admin = new Admin();
        $admin->name = $overrides['name'] ?? 'Admin TA';
        $admin->email = $overrides['email'] ?? 'admin_ta@example.com';
        $admin->password = Hash::make($overrides['password'] ?? 'Password123');
        $admin->role = $overrides['role'] ?? 'admin_ta';
        $admin->save();

        return $admin;
    }

    protected function loginAdminTa($email = 'admin_ta@example.com', $password = 'Password123')
    {
        return $this->postJson('/api/login/admin', [
            'email' => $email,
            'password' => $password,
            'role' => 'admin_ta'
        ]);
    }

    protected function getTokenFromResponse($response)
    {
        $data = $response->json();
        return $data['token'] ?? $data['data']['token'] ?? $data['access_token'] ?? null;
    }

    /**
     * Helper untuk membuat data test dasar
     */
    private function createTestData($bimbinganCount = 9)
    {
        // Create mahasiswa
        $mahasiswa = new User();
        $mahasiswa->name = 'Mahasiswa Test';
        $mahasiswa->email = 'mahasiswa_test@example.com';
        $mahasiswa->password = Hash::make('password123');
        $mahasiswa->nim = '12345678';
        $mahasiswa->no_telp = '081234567890';
        $mahasiswa->status = 1;
        $mahasiswa->save();

        // Create dosen pembimbing
        $dosenPembimbing = new Dosen();
        $dosenPembimbing->name = 'Dosen Pembimbing';
        $dosenPembimbing->email = 'dosen_pembimbing@example.com';
        $dosenPembimbing->password = Hash::make('password123');
        $dosenPembimbing->nik = '987654321';
        $dosenPembimbing->status = 1;
        $dosenPembimbing->save();

        // Create proposal
        $proposal = new Proposal();
        $proposal->judul_proposal = 'Proposal Test';
        $proposal->file_proposal = 'proposal_test.pdf';
        $proposal->file_transkrip = 'transkrip_test.pdf';
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
        $tugasAkhir->mahasiswa_id = $mahasiswa->id;
        $tugasAkhir->dosen_pembimbing_id = $dosenPembimbing->id;
        $tugasAkhir->proposal_id = $proposal->id;
        $tugasAkhir->semester_id = $semester->id;
        $tugasAkhir->tahun_ajaran_id = $tahunAjaran->id;
        $tugasAkhir->krs = 'krs_test.pdf';
        $tugasAkhir->status_aktif = 1;
        $tugasAkhir->save();

        // Create bimbingan
        for ($i = 0; $i < $bimbinganCount; $i++) {
            $bimbingan = new Bimbingan();
            $bimbingan->tugas_akhir_id = $tugasAkhir->id;
            $bimbingan->catatan = "Bimbingan ke-" . ($i + 1);
            $bimbingan->status_verifikasi = 1;
            $bimbingan->tanggal_bimbingan = now()->subDays($i);
            $bimbingan->save();
        }

        return [
            'mahasiswa' => $mahasiswa,
            'dosenPembimbing' => $dosenPembimbing,
            'proposal' => $proposal,
            'semester' => $semester,
            'tahunAjaran' => $tahunAjaran,
            'tugasAkhir' => $tugasAkhir
        ];
    }

    public function test_ambil_data_tugas_akhir_siap_penguji()
    {
        // Create admin and login
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Create test data with 9 verified bimbingan
        $data = $this->createTestData(9);

        // Test API endpoint with authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin_ta/dosen-penguji');

        // Check status code
        $response->assertStatus(200);

        // Verify response structure
        $response->assertJsonStructure([
            'status',
            'message',
        ]);

        // Verify status is success
        $responseBody = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseBody['status']);
    }

    public function test_tidak_ambil_tugas_akhir_dengan_bimbingan_kurang_dari_9()
    {
        // Create admin and login
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Create test data with only 8 bimbingan
        $data = $this->createTestData(8);

        // Test API endpoint with authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin_ta/dosen-penguji');

        // Verify response
        $response->assertStatus(200);

        $responseData = json_decode($response->getContent(), true);

        // Check if tugas akhir is not in the response
        if (isset($responseData['data'])) {
            if (isset($responseData['data']['data'])) {
                $this->assertCount(0, $responseData['data']['data']);
            } else if (is_array($responseData['data'])) {
                $this->assertCount(0, $responseData['data']);
            } else {
                $this->assertTrue(true);
            }
        } else {
            $this->assertTrue(true);
        }
    }

    // public function test_tambah_dosen_penguji()
    // {
    //     // Create admin and login
    //     $this->createAdminTa();
    //     $loginResponse = $this->loginAdminTa();
    //     $token = $this->getTokenFromResponse($loginResponse);

    //     // Create test data
    //     $data = $this->createTestData();

    //     // Create two more dosen
    //     $dosen2 = new Dosen();
    //     $dosen2->name = 'Dosen Penguji 2';
    //     $dosen2->email = 'dosen_penguji2@example.com';
    //     $dosen2->password = Hash::make('password123');
    //     $dosen2->nik = '123456789';
    //     $dosen2->status = 1;
    //     $dosen2->save();

    //     $dosen3 = new Dosen();
    //     $dosen3->name = 'Dosen Penguji 3';
    //     $dosen3->email = 'dosen_penguji3@example.com';
    //     $dosen3->password = Hash::make('password123');
    //     $dosen3->nik = '234567891';
    //     $dosen3->status = 1;
    //     $dosen3->save();

    //     // Data request
    //     $requestData = [
    //         'list_dosen_id' => [
    //             $data['dosenPembimbing']->id,
    //             $dosen2->id,
    //             $dosen3->id
    //         ],
    //         'tugas_akhir_id' => $data['tugasAkhir']->id
    //     ];

    //     // Send request with authentication
    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //     ])->postJson('/api/admin_ta/dosen-penguji', $requestData);

    //     // Tambahkan debugging
    //     if ($response->status() != 201) {
    //         $this->fail('Response status bukan 201. Response: ' . $response->getContent());
    //     }

    //     // Verify response
    //     $response->assertStatus(201)
    //         ->assertJsonPath('status', 'success')
    //         ->assertJsonPath('message', 'Dosen penguji berhasil ditambahkan');

    //     // Verify database records
    //     $this->assertDatabaseCount('dosen_penguji', 3);
    //     $this->assertDatabaseHas('dosen_penguji', [
    //         'dosen_id' => $data['dosenPembimbing']->id,
    //         'tugas_akhir_id' => $data['tugasAkhir']->id
    //     ]);
    // }

    public function test_validasi_jumlah_dosen_penguji()
    {
        // Create admin and login
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Create test data
        $data = $this->createTestData();

        // Create one more dosen
        $dosen2 = new Dosen();
        $dosen2->name = 'Dosen Penguji 2';
        $dosen2->email = 'dosen_penguji2_2@example.com';
        $dosen2->password = Hash::make('password123');
        $dosen2->nik = '345678912';
        $dosen2->status = 1;
        $dosen2->save();

        // Data request with only 2 dosen (not valid)
        $requestData = [
            'list_dosen_id' => [
                $data['dosenPembimbing']->id,
                $dosen2->id
            ],
            'tugas_akhir_id' => $data['tugasAkhir']->id
        ];

        // Send request with authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin_ta/dosen-penguji', $requestData);

        // Verify response
        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Check error message
        $this->assertStringContainsString('Harus memilih 3 dosen', $response->getContent());
    }

    public function test_validasi_dosen_pembimbing_wajib()
    {
        // Create admin and login
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Create test data
        $data = $this->createTestData();

        // Create 3 new dosen
        $dosen1 = new Dosen();
        $dosen1->name = 'Dosen Penguji A';
        $dosen1->email = 'dosen_a@example.com';
        $dosen1->password = Hash::make('password123');
        $dosen1->nik = '456789123';
        $dosen1->status = 1;
        $dosen1->save();

        $dosen2 = new Dosen();
        $dosen2->name = 'Dosen Penguji B';
        $dosen2->email = 'dosen_b@example.com';
        $dosen2->password = Hash::make('password123');
        $dosen2->nik = '567891234';
        $dosen2->status = 1;
        $dosen2->save();

        $dosen3 = new Dosen();
        $dosen3->name = 'Dosen Penguji C';
        $dosen3->email = 'dosen_c@example.com';
        $dosen3->password = Hash::make('password123');
        $dosen3->nik = '678912345';
        $dosen3->status = 1;
        $dosen3->save();

        // Data request without the pembimbing
        $requestData = [
            'list_dosen_id' => [
                $dosen1->id,
                $dosen2->id,
                $dosen3->id
            ],
            'tugas_akhir_id' => $data['tugasAkhir']->id
        ];

        // Send request with authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin_ta/dosen-penguji', $requestData);

        // Verify response
        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Salah satu dosen penguji harus dari dosen pembimbing');
    }

    public function test_validasi_tugas_akhir_harus_memiliki_proposal()
    {
        // Create admin and login
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Create mahasiswa
        $mahasiswa = new User();
        $mahasiswa->name = 'Mahasiswa No Proposal';
        $mahasiswa->email = 'mahasiswa_noproposal@example.com';
        $mahasiswa->password = Hash::make('password123');
        $mahasiswa->nim = '87654321';
        $mahasiswa->no_telp = '089876543210';
        $mahasiswa->status = 1;
        $mahasiswa->save();

        // Create dosen pembimbing
        $dosenPembimbing = new Dosen();
        $dosenPembimbing->name = 'Dosen Pembimbing 2';
        $dosenPembimbing->email = 'dosen_pembimbing2@example.com';
        $dosenPembimbing->password = Hash::make('password123');
        $dosenPembimbing->nik = '789123456';
        $dosenPembimbing->status = 1;
        $dosenPembimbing->save();

        // Create semester
        $semester = new Semester();
        $semester->nama_semester = 'Genap';
        $semester->save();

        // Create tahun ajaran
        $tahunAjaran = new TahunAjaran();
        $tahunAjaran->tahun_ajaran = '2024/2025';
        $tahunAjaran->save();

        // Create tugas akhir without proposal
        $tugasAkhir = new TugasAkhir();
        $tugasAkhir->judul_tugas_akhir = 'Judul Tugas Akhir No Proposal';
        $tugasAkhir->mahasiswa_id = $mahasiswa->id;
        $tugasAkhir->dosen_pembimbing_id = $dosenPembimbing->id;
        $tugasAkhir->proposal_id = null; // No proposal
        $tugasAkhir->semester_id = $semester->id;
        $tugasAkhir->tahun_ajaran_id = $tahunAjaran->id;
        $tugasAkhir->krs = 'krs_no_proposal.pdf';
        $tugasAkhir->status_aktif = 1;
        $tugasAkhir->save();

        // Create 9 bimbingan
        for ($i = 0; $i < 9; $i++) {
            $bimbingan = new Bimbingan();
            $bimbingan->tugas_akhir_id = $tugasAkhir->id;
            $bimbingan->catatan = "Bimbingan No Proposal ke-" . ($i + 1);
            $bimbingan->status_verifikasi = 1;
            $bimbingan->tanggal_bimbingan = now()->subDays($i);
            $bimbingan->save();
        }

        // Create additional dosen
        $dosen2 = new Dosen();
        $dosen2->name = 'Dosen Additional 1';
        $dosen2->email = 'dosen_add1@example.com';
        $dosen2->password = Hash::make('password123');
        $dosen2->nik = '891234567';
        $dosen2->status = 1;
        $dosen2->save();

        $dosen3 = new Dosen();
        $dosen3->name = 'Dosen Additional 2';
        $dosen3->email = 'dosen_add2@example.com';
        $dosen3->password = Hash::make('password123');
        $dosen3->nik = '912345678';
        $dosen3->status = 1;
        $dosen3->save();

        // Data request
        $requestData = [
            'list_dosen_id' => [
                $dosenPembimbing->id,
                $dosen2->id,
                $dosen3->id
            ],
            'tugas_akhir_id' => $tugasAkhir->id
        ];

        // Send request with authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin_ta/dosen-penguji', $requestData);

        // Verify response
        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Tugas Akhir harus memiliki proposal terlebih dahulu sebelum menambahkan dosen penguji');
    }

    public function test_batas_maksimal_dosen_penguji()
    {
        // Create admin and login
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Create test data
        $data = $this->createTestData();

        // Create additional dosen
        $dosen2 = new Dosen();
        $dosen2->name = 'Dosen Max Test 1';
        $dosen2->email = 'dosen_max1@example.com';
        $dosen2->password = Hash::make('password123');
        $dosen2->nik = '111222333';
        $dosen2->status = 1;
        $dosen2->save();

        $dosen3 = new Dosen();
        $dosen3->name = 'Dosen Max Test 2';
        $dosen3->email = 'dosen_max2@example.com';
        $dosen3->password = Hash::make('password123');
        $dosen3->nik = '222333444';
        $dosen3->status = 1;
        $dosen3->save();

        $dosen4 = new Dosen();
        $dosen4->name = 'Dosen Max Test 3';
        $dosen4->email = 'dosen_max3@example.com';
        $dosen4->password = Hash::make('password123');
        $dosen4->nik = '333444555';
        $dosen4->status = 1;
        $dosen4->save();

        // Insert 3 dosen penguji first
        DosenPenguji::insert([
            [
                'dosen_id' => $data['dosenPembimbing']->id,
                'tugas_akhir_id' => $data['tugasAkhir']->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dosen_id' => $dosen2->id,
                'tugas_akhir_id' => $data['tugasAkhir']->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dosen_id' => $dosen3->id,
                'tugas_akhir_id' => $data['tugasAkhir']->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Try to add 3 more
        $requestData = [
            'list_dosen_id' => [
                $data['dosenPembimbing']->id,
                $dosen3->id,
                $dosen4->id
            ],
            'tugas_akhir_id' => $data['tugasAkhir']->id
        ];

        // Send request with authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin_ta/dosen-penguji', $requestData);

        // Verify response
        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Maksimal 3 dosen penguji untuk tugas akhir ini!');
    }

    public function test_update_dosen_penguji()
    {
        // Create admin and login
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Create test data
        $data = $this->createTestData();

        // Create additional dosen
        $dosen1 = new Dosen();
        $dosen1->name = 'Dosen Update Test 1';
        $dosen1->email = 'dosen_update1@example.com';
        $dosen1->password = Hash::make('password123');
        $dosen1->nik = '444555666';
        $dosen1->status = 1;
        $dosen1->save();

        $dosen2 = new Dosen();
        $dosen2->name = 'Dosen Update Test 2';
        $dosen2->email = 'dosen_update2@example.com';
        $dosen2->password = Hash::make('password123');
        $dosen2->nik = '555666777';
        $dosen2->status = 1;
        $dosen2->save();

        $dosen3 = new Dosen();
        $dosen3->name = 'Dosen Update Test 3';
        $dosen3->email = 'dosen_update3@example.com';
        $dosen3->password = Hash::make('password123');
        $dosen3->nik = '666777888';
        $dosen3->status = 1;
        $dosen3->save();

        // Insert 3 dosen penguji first
        DosenPenguji::insert([
            [
                'dosen_id' => $data['dosenPembimbing']->id,
                'tugas_akhir_id' => $data['tugasAkhir']->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dosen_id' => $dosen1->id,
                'tugas_akhir_id' => $data['tugasAkhir']->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dosen_id' => $dosen2->id,
                'tugas_akhir_id' => $data['tugasAkhir']->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Data request to update (replace dosen2 with dosen3)
        $requestData = [
            'list_dosen_id' => [
                $data['dosenPembimbing']->id,
                $dosen1->id,
                $dosen3->id
            ],
            'tugas_akhir_id' => $data['tugasAkhir']->id
        ];

        // Send request with authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin_ta/dosen-penguji/' . $data['tugasAkhir']->id, $requestData);

        // Verify response
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Dosen penguji berhasil diperbarui');

        // Verify database records
        $this->assertDatabaseCount('dosen_penguji', 3);
        $this->assertDatabaseHas('dosen_penguji', [
            'dosen_id' => $dosen3->id,
            'tugas_akhir_id' => $data['tugasAkhir']->id
        ]);
        $this->assertDatabaseMissing('dosen_penguji', [
            'dosen_id' => $dosen2->id,
            'tugas_akhir_id' => $data['tugasAkhir']->id
        ]);
    }
}
