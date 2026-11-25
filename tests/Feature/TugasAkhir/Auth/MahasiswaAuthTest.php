<?php

namespace Tests\Feature\TugasAkhir\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class MahasiswaAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function createMahasiswa(array $overrides = [])
    {
        return User::create(array_merge([
            'nim' => '1234567890',
            'name' => 'Test Mahasiswa',
            'email' => 'mahasiswa@example.com',
            'no_telp' => '08123456789',
            'password' => Hash::make('Password123'),
            'status' => 1,
        ], $overrides));
    }

    protected function loginMahasiswa($email = 'mahasiswa@example.com', $password = 'Password123')
    {
        return $this->postJson('/api/login/mahasiswa', compact('email', 'password'));
    }

    protected function getTokenFromResponse($response)
    {
        $data = $response->json();
        return $data['token'] ?? $data['data']['token'] ?? $data['access_token'] ?? null;
    }

    public function test_login_sukses()
    {
        $this->createMahasiswa();

        $response = $this->loginMahasiswa();

        $response->assertStatus(200);
        $this->assertTrue($response->json('status') === true);

        $token = $this->getTokenFromResponse($response);
        $this->assertNotNull($token);
    }

    public function test_logout_sukses()
    {
        $this->createMahasiswa();

        $response = $this->loginMahasiswa();
        $token = $this->getTokenFromResponse($response);

        if ($token) {
            $logout = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->postJson('/api/logout');

            $logout->assertStatus(204);
        } else {
            $this->markTestSkipped('Token tidak ditemukan dalam respons: ' . json_encode($response->json()));
        }
    }

    public function test_login_password_salah()
    {
        $this->createMahasiswa();

        $response = $this->loginMahasiswa(password: 'WrongPassword123');

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Email atau password salah',
            ]);
    }

    public function test_login_email_salah()
    {
        $response = $this->loginMahasiswa(email: 'nonexistent@example.com');

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Email atau password salah',
            ]);
    }

    public function test_login_form_kosong()
    {
        $response = $this->postJson('/api/login/mahasiswa', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(401);
        $this->assertArrayHasKey('message', $response->json());
    }
    public function test_gagal_login_ke_endpoint_admin()
    {
        $response = $this->postJson('/api/login/admin', [
            'email' => 'mahasiswa@example.com',
            'password' => 'Password123'
        ]);

        // Combine assertions
        $response->assertStatus(401)
            ->assertJson(['message' => 'Email atau password salah.']);
    }
    public function test_gagal_login_ke_endpoint_dosen()
    {
        $response = $this->postJson('/api/login/dosen', [
            'email' => 'mahasiswa@example.com',
            'password' => 'Password123'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Email atau password salah'
            ]);
    }
}
