<?php

namespace Tests\Feature\TugasAkhir;

use Tests\TestCase;
use App\Models\Sesi;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SesiControllerTest extends TestCase
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
     * Test mendapatkan daftar sesi
     */
    public function test_ambil_data_sesi()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        Sesi::create([
            'nama_sesi' => 'Sesi Pagi',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00'
        ]);

        Sesi::create([
            'nama_sesi' => 'Sesi Siang',
            'waktu_mulai' => '13:00:00',
            'waktu_selesai' => '15:00:00'
        ]);

        Sesi::create([
            'nama_sesi' => 'Sesi Sore',
            'waktu_mulai' => '16:00:00',
            'waktu_selesai' => '18:00:00'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin_ta/sesi');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'nama_sesi', 'waktu_mulai', 'waktu_selesai']
                ]
            ]);
    }

    /**
     * Test mendapatkan detail sesi berdasarkan ID
     */
    public function test_ambil_data_sesi_berdasarkan_id()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat sesi secara manual
        $sesi = Sesi::create([
            'nama_sesi' => 'Sesi Detail',
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin_ta/sesi/{$sesi->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'nama_sesi', 'waktu_mulai', 'waktu_selesai']
            ]);
    }

    /**
     * Test membuat sesi baru
     */
    public function test_membuat_data_sesi()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $sesiData = [
            'nama_sesi' => 'Sesi Test',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin_ta/sesi', $sesiData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'nama_sesi', 'waktu_mulai', 'waktu_selesai']
            ]);

        $this->assertDatabaseHas('sesi', $sesiData);
    }

    /**
     * Test update sesi
     */
    public function test_edit_data_sesi()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat sesi secara manual
        $sesi = Sesi::create([
            'nama_sesi' => 'Sesi Original',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00'
        ]);

        $updatedData = [
            'nama_sesi' => 'Sesi Updated',
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin_ta/sesi/{$sesi->id}", $updatedData);

        $response->assertStatus(201)
            ->assertJsonPath('data.nama_sesi', 'Sesi Updated');

        $this->assertDatabaseHas('sesi', $updatedData);
    }

    /**
     * Test menghapus sesi
     */
    public function test_delete_data_sesi()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat sesi secara manual
        $sesi = Sesi::create([
            'nama_sesi' => 'Sesi Hapus',
            'waktu_mulai' => '14:00:00',
            'waktu_selesai' => '16:00:00'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin_ta/sesi/{$sesi->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Data sesi berhasil dihapus');

        $this->assertDatabaseMissing('sesi', ['id' => $sesi->id]);
    }

    /**
     * Test validasi waktu selesai harus setelah waktu mulai
     */
    public function test_validasi_waktu_sesi_tidak_valid()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $sesiData = [
            'nama_sesi' => 'Sesi Invalid',
            'waktu_mulai' => '10:00:00',
            'waktu_selesai' => '08:00:00'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin_ta/sesi', $sesiData);

        $response->assertStatus(422);
    }
    public function test_gagal_ambil_sesi_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin_ta/sesi/999");

        $response->assertStatus(404);
    }

    public function test_gagal_edit_sesi_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin_ta/sesi/999", [
            'nama_sesi' => 'Sesi update',
            'waktu_mulai' => '10:00:00',
            'waktu_selesai' => '08:00:00'
        ]);

        $response->assertStatus(500);
    }

    public function test_gagal_hapus_sesi_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin_ta/sesi/999");

        $response->assertStatus(500);
    }
}
