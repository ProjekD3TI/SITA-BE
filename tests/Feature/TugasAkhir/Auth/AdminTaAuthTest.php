<?php

namespace Tests\Feature\TugasAkhir\Auth;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminTaAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminTa(array $overrides = [])
    {
        return Admin::factory()->create(array_merge([
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

    public function test_login_sukses()
    {
        $this->createAdminTa();

        $response = $this->loginAdminTa();

        $response->assertStatus(200);

        // Verifikasi respons sesuai dengan format yang diharapkan
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'admin',
                'token'
            ]
        ]);

        // Verifikasi isi respons
        $responseData = $response->json();
        $this->assertTrue($responseData['status']);
        $this->assertEquals('Berhasil Login', $responseData['message']);

        // Verifikasi token
        $token = $this->getTokenFromResponse($response);
        $this->assertNotNull($token);
    }

    public function test_login_password_salah()
    {
        $this->createAdminTa();

        $response = $this->loginAdminTa(password: 'WrongPassword123');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email atau password salah.'
            ]);
    }

    public function test_login_email_tidak_valid()
    {
        $response = $this->loginAdminTa(email: 'nonexistent@example.com');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email atau password salah.'
            ]);
    }

    public function test_login_form_kosong()
    {
        $response = $this->postJson('/api/login/admin', [
            'email' => '',
            'password' => '',
            'role' => 'admin_ta'
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('message', $response->json());
        $this->assertArrayHasKey('errors', $response->json());
        $this->assertArrayHasKey('email', $response->json('errors'));
        $this->assertArrayHasKey('password', $response->json('errors'));
    }

    public function test_logout_sukses()
    {
        $this->createAdminTa();

        $response = $this->loginAdminTa();
        $token = $this->getTokenFromResponse($response);

        if ($token) {
            $logout = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->postJson('/api/logout');

            $logout->assertStatus(204);
        } else {
            $this->markTestSkipped('Token tidak ditemukan dalam respons: ' . json_encode($response->json()));
        }
    }
    public function test_gagal_login_ke_endpoint_mahasiswa()
    {
        $response = $this->postJson('/api/login/mahasiswa', [
            'email' => 'dosen@example.com',
            'password' => 'Password123'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Email atau password salah'
            ]);
    }
    public function test_gagal_login_ke_endpoint_dosen()
    {
        $response = $this->postJson('/api/login/dosen', [
            'email' => 'mahasiswa@example.com',
            'password' => 'Password123'
        ]);

        // Check response
        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Email atau password salah'
            ]);
    }
}
