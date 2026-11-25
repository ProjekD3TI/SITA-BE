<?php

namespace Tests\Feature\TugasAkhir;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TahunAjaranControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminTa(array $overrides = [])
    {
        return Admin::create(array_merge([
            'name' => 'Admin TA',  // Tambahkan kolom name yang diperlukan
            'email' => 'admin_ta@example.com',
            'password' => Hash::make('Password123'),
            'role' => 'admin_ta',
        ], $overrides));
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
     * Test mendapatkan daftar tahun ajaran
     */
    public function test_ambil_data_tahun_ajaran()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat beberapa tahun ajaran secara manual
        TahunAjaran::create(['tahun_ajaran' => '2023/2024']);
        TahunAjaran::create(['tahun_ajaran' => '2024/2025']);
        TahunAjaran::create(['tahun_ajaran' => '2025/2026']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin_ta/tahun-ajaran');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'tahun_ajaran']
                ]
            ]);
    }

    /**
     * Test mendapatkan detail tahun ajaran berdasarkan ID
     */
    public function test_ambil_data_tahun_ajaran_berdasarkan_id()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat tahun ajaran secara manual
        $tahunAjaran = TahunAjaran::create(['tahun_ajaran' => '2023/2024']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin_ta/tahun-ajaran/{$tahunAjaran->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'tahun_ajaran']
            ]);
    }

    /**
     * Test membuat tahun ajaran baru
     */
    public function test_membuat_data_tahun_ajaran()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $tahunAjaranData = [
            'tahun_ajaran' => '2025/2026'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin_ta/tahun-ajaran', $tahunAjaranData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'tahun_ajaran']
            ]);

        $this->assertDatabaseHas('tahun_ajaran', $tahunAjaranData);
    }

    /**
     * Test update tahun ajaran
     */
    public function test_edit_data_tahun_ajaran()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat tahun ajaran secara manual
        $tahunAjaran = TahunAjaran::create(['tahun_ajaran' => '2024/2025']);

        $updatedData = [
            'tahun_ajaran' => '2026/2027'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin_ta/tahun-ajaran/{$tahunAjaran->id}", $updatedData);

        $response->assertStatus(201)
            ->assertJsonPath('data.tahun_ajaran', '2026/2027');

        $this->assertDatabaseHas('tahun_ajaran', $updatedData);
    }

    /**
     * Test menghapus tahun ajaran
     */
    public function test_delete_data_tahun_ajaran()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat tahun ajaran secara manual
        $tahunAjaran = TahunAjaran::create(['tahun_ajaran' => '2022/2023']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin_ta/tahun-ajaran/{$tahunAjaran->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('tahun_ajaran', ['id' => $tahunAjaran->id]);
    }

    /**
     * Test gagal mendapatkan tahun ajaran yang tidak ada
     */
    public function test_gagal_ambil_tahun_ajaran_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin_ta/tahun-ajaran/999");

        $response->assertStatus(404);
    }

    /**
     * Test gagal edit tahun ajaran yang tidak ada
     */
    public function test_gagal_edit_tahun_ajaran_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin_ta/tahun-ajaran/999", [
            'tahun_ajaran' => '2027/2028'
        ]);

        $response->assertStatus(500);
    }

    /**
     * Test gagal hapus tahun ajaran yang tidak ada
     */
    public function test_gagal_hapus_tahun_ajaran_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin_ta/tahun-ajaran/999");

        $response->assertStatus(500);
    }
}
