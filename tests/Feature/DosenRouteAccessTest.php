<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Jabatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class DosenRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    private $dosen;
    private $jabatanDosen;
    private $jabatanKalab;
    private $jabatanKaprodi;
    private $jabatanPelaporan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jabatanDosen = Jabatan::create([
            'name' => 'Dosen',
            'description' => 'Dosen Biasa'
        ]);

        $this->jabatanKalab = Jabatan::create([
            'name' => 'Kalab',
            'description' => 'Kepala Laboratorium'
        ]);

        $this->jabatanKaprodi = Jabatan::create([
            'name' => 'Kaprodi',
            'description' => 'Kepala Program Studi'
        ]);

        $this->jabatanPelaporan = Jabatan::create([
            'name' => 'Pelaporan',
            'description' => 'Petugas Pelaporan'
        ]);

        $this->dosen = Dosen::create([
            'name' => 'Test Dosen',
            'email' => 'testdosen@example.com',
            'password' => bcrypt('password123'),
            'nik' => '12345678',
            'status' => 1
        ]);

        $this->dosen->jabatan()->attach($this->jabatanDosen->id);
    }

    private function addRole($additionalRole)
    {
        if ($additionalRole !== 'Dosen') {
            $jabatan = Jabatan::where('name', $additionalRole)->first();
            $this->dosen->jabatan()->attach($jabatan->id);
        }
        Sanctum::actingAs($this->dosen, ['*']);
    }

    public function test_all_users_have_dosen_access()
    {
        Sanctum::actingAs($this->dosen, ['*']);
        $response = $this->getJson('/api/dosen/dashboard');
        $response->assertStatus(200);

        $this->addRole('Kalab');
        $response = $this->getJson('/api/dosen/dashboard');
        $response->assertStatus(200);

        $this->dosen->jabatan()->detach($this->jabatanKalab->id);
        $this->addRole('Kaprodi');
        $response = $this->getJson('/api/dosen/dashboard');
        $response->assertStatus(200);

        $this->dosen->jabatan()->detach($this->jabatanKaprodi->id);
        $this->addRole('Pelaporan');
        $response = $this->getJson('/api/dosen/dashboard');
        $response->assertStatus(200);
    }

    public function test_regular_dosen_cannot_access_special_roles()
    {
        Sanctum::actingAs($this->dosen, ['*']);
        
        $response = $this->getJson('/api/kalab/dashboard');
        $response->assertStatus(403);

        $response = $this->getJson('/api/kaprodi/dashboard');
        $response->assertStatus(403);

        $response = $this->getJson('/api/pelaporan/dashboard');
        $response->assertStatus(403);
    }

    public function test_kalab_specific_access()
    {
        $this->addRole('Kalab');
        
        $response = $this->getJson('/api/kalab/dashboard');
        $response->assertStatus(200);
        
        $response = $this->getJson('/api/kaprodi/dashboard');
        $response->assertStatus(403);
        $response = $this->getJson('/api/pelaporan/dashboard');
        $response->assertStatus(403);
    }

    public function test_kaprodi_specific_access()
    {
        $this->addRole('Kaprodi');
        
        $response = $this->getJson('/api/kaprodi/dashboard');
        $response->assertStatus(200);
        
        $response = $this->getJson('/api/kalab/dashboard');
        $response->assertStatus(403);
        $response = $this->getJson('/api/pelaporan/dashboard');
        $response->assertStatus(403);
    }

    public function test_pelaporan_specific_access()
    {
        $this->addRole('Pelaporan');
        
        $response = $this->getJson('/api/pelaporan/dashboard');
        $response->assertStatus(200);
        
        $response = $this->getJson('/api/kalab/dashboard');
        $response->assertStatus(403);
        $response = $this->getJson('/api/kaprodi/dashboard');
        $response->assertStatus(403);
    }

    public function test_multiple_special_roles()
    {
        $this->addRole('Kalab');
        $this->addRole('Kaprodi');
        
        $response = $this->getJson('/api/dosen/dashboard');
        $response->assertStatus(200);
        
        $response = $this->getJson('/api/kalab/dashboard');
        $response->assertStatus(200);
        $response = $this->getJson('/api/kaprodi/dashboard');
        $response->assertStatus(200);
        
        $response = $this->getJson('/api/pelaporan/dashboard');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_access()
    {
        $response = $this->getJson('/api/dosen/dashboard');
        $response->assertStatus(401);
    }
}