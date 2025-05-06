<?php

namespace Tests\Feature\TugasAkhir;

use Tests\TestCase;
use App\Models\Ruang;
use App\Models\Admin;
use App\Models\UjianTA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RuangControllerTest extends TestCase
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
    public function test_ambil_data_ruang()
    {

        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);


        Ruang::create(['nama_ruang' => 'Ruang A']);
        Ruang::create(['nama_ruang' => 'Ruang B']);
        Ruang::create(['nama_ruang' => 'Ruang C']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin_ta/ruang');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'nama_ruang']
                ]
            ]);
    }

    public function test_ambil_data_ruang_berdasarkan_id()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Ganti factory dengan create
        $ruang = Ruang::create(['nama_ruang' => 'Ruang Khusus']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin_ta/ruang/{$ruang->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'nama_ruang']
            ]);
    }

    public function test_membuat_data_ruang()
    {

        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $ruangData = [
            'nama_ruang' => 'Ruang Test'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin_ta/ruang', $ruangData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'nama_ruang']
            ]);

        $this->assertDatabaseHas('ruang', $ruangData);
    }

    public function test_edit_data_ruang()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        // Ganti factory dengan create
        $ruang = Ruang::create(['nama_ruang' => 'Ruang Lama']);

        $updatedData = [
            'nama_ruang' => 'Ruang Updated'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin_ta/ruang/{$ruang->id}", $updatedData);

        $response->assertStatus(201)
            ->assertJsonPath('data.nama_ruang', 'Ruang Updated');

        $this->assertDatabaseHas('ruang', $updatedData);
    }

    public function test_delete_data_ruang()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $ruang = Ruang::create(['nama_ruang' => 'Ruang Hapus']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin_ta/ruang/{$ruang->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Data sesi berhasil ditambahkan'); // Sesuai dengan pesan di controller

        $this->assertDatabaseMissing('ruang', ['id' => $ruang->id]);
    }
    public function test_gagal_ambil_ruang_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin_ta/ruang/999");

        $response->assertStatus(404);
    }

    public function test_gagal_edit_ruang_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin_ta/ruang/999", [
            'nama_ruang' => 'Ruang Updated'
        ]);

        $response->assertStatus(500);
    }

    public function test_gagal_hapus_ruang_tidak_ada()
    {
        $this->createAdminTa();
        $loginResponse = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($loginResponse);


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin_ta/ruang/999");

        $response->assertStatus(500);
    }
}
