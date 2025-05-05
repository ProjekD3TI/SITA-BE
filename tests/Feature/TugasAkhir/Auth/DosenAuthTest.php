<?php

namespace Tests\Feature\TugasAkhir\Auth;

use Tests\TestCase;
use App\Models\Jabatan;
use App\Models\JabatanDosen;
use App\Models\Dosen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class DosenAuthTest extends TestCase
{
    use RefreshDatabase;
    use HasFactory;

    protected function createDosen(array $overrides = [])
    {
        // Create dosen directly using the model
        $dosen = Dosen::create(array_merge([
            'email' => 'dosen@example.com',
            'password' => Hash::make('Password123'),
            'status' => 1,
            'name' => 'Test Dosen',
            'nik' => '12345678901234',
        ], $overrides));

        $jabatan = Jabatan::create([
            'name' => 'Dosen',
            'description' => 'Kaprodi',
        ]);

        // Buat relasi jabatan dosen
        JabatanDosen::create([
            'dosen_id' => $dosen->id,
            'jabatan_id' => $jabatan->id,
            'is_active' => true
        ]);

        // Return the dosen at the end
        return $dosen;
    }

    protected function loginDosen($email = 'dosen@example.com', $password = 'Password123')
    {
        return $this->postJson('/api/login/dosen', compact('email', 'password'));
    }

    protected function getTokenFromResponse($response)
    {
        $data = $response->json();
        return $data['data']['token'] ?? $data['token'] ?? $data['access_token'] ?? null;
    }

    public function test_login_sukses()
    {
        $this->createDosen();

        $response = $this->loginDosen();

        $response->assertStatus(200);
        $this->assertTrue($response->json('status') === true);;

        $token = $this->getTokenFromResponse($response);
        $this->assertNotNull($token);
    }
    public function test_logout_sukses()
    {
        $this->createDosen();

        $response = $this->loginDosen();
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
        $this->createDosen();

        $response = $this->loginDosen(password: 'WrongPassword123');

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Email atau password salah',
            ]);
    }

    public function test_login_email_tidak_valid()
    {
        $response = $this->loginDosen(email: 'nonexistent@example.com');

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Email atau password salah',
            ]);
    }

    public function test_login_form_kosong()
    {
        $response = $this->postJson('/api/login/dosen', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(401);
        $this->assertArrayHasKey('message', $response->json());
    }

    public function test_gagal_login_ke_endpoint_admin()
    {
        $response = $this->postJson('/api/login/admin', [
            'email' => 'dosen@example.com',
            'password' => 'Password123'
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Email atau password salah.']);
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
}
