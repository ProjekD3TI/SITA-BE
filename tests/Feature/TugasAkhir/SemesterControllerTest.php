<?php

namespace Tests\Feature\TugasAkhir;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Semester;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SemesterControllerTest extends TestCase
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
     * Test mendapatkan daftar semester
     */
    public function test_ambil_data_semester()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat semester secara manual
        Semester::create(['nama_semester' => 'Ganjil']);
        Semester::create(['nama_semester' => 'Genap']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin_ta/semester');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'nama_semester']
                ]
            ]);
    }

    /**
     * Test mendapatkan detail semester berdasarkan ID
     */
    public function test_ambil_data_semester_berdasarkan_id()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat semester secara manual
        $semester = Semester::create(['nama_semester' => 'Ganjil']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin_ta/semester/{$semester->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'nama_semester']
            ]);
    }

    /**
     * Test update data semester
     */
    public function test_edit_data_semester()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Buat semester secara manual
        $semester = Semester::create(['nama_semester' => 'Ganjil']);

        $updatedData = [
            'nama_semester' => 'Antara'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin_ta/semester/{$semester->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_semester', 'Antara');

        $this->assertDatabaseHas('semester', $updatedData);
    }

    /**
     * Test get semester yang tidak ditemukan
     */
    public function test_get_semester_tidak_ditemukan()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin_ta/semester/999"); // ID yang tidak ada

        $response->assertStatus(404);
    }

    /**
     * Test edit semester yang tidak ditemukan
     */
    public function test_edit_semester_tidak_ditemukan()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $updatedData = [];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin_ta/semester/999", $updatedData); // ID yang tidak ada

        $response->assertStatus(500);
    }
}
