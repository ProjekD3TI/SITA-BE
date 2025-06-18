<?php

namespace Tests\Feature\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\Bhp;
use App\Models\AdminPengelola\Gedung;
use App\Models\AdminPengelola\Inventaris;
use App\Models\AdminPengelola\Ruang;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPengelolaControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test dashboard counts with large datasets
    public function test_dashboard_counts_with_large_datasets()
    {
        // Create 150 records for each model
        Inventaris::factory()->count(150)->create();
        Bhp::factory()->count(150)->create();
        Ruang::factory()->count(150)->create();
        Gedung::factory()->count(150)->create();

        $response = $this->getJson('/api/admin-pengelola/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'total_inventaris' => 150,
                    'total_bhp' => 150,
                    'total_ruang' => 150,
                    'total_gedung' => 150,
                ]
            ]);
    }

    // Test BHP pagination with more than 100 items
    public function test_show_bhp_with_more_than_100_items()
    {
        Bhp::factory()->count(150)->create();

        // Test default pagination (page 1, 10 items)
        $response = $this->getJson('/api/admin-pengelola/bhp');
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJson([
                'pagination' => [
                    'total' => 150,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 15,
                ]
            ]);

        // Test page 2
        $response = $this->getJson('/api/admin-pengelola/bhp?page=2');
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJson([
                'pagination' => [
                    'current_page' => 2,
                ]
            ]);

        // Test custom per_page
        $response = $this->getJson('/api/admin-pengelola/bhp?per_page=50');
        $response->assertStatus(200)
            ->assertJsonCount(50, 'data')
            ->assertJson([
                'pagination' => [
                    'per_page' => 50,
                    'last_page' => 3,
                ]
            ]);
    }

    // Test BHP search with large dataset
    public function test_bhp_search_with_large_dataset()
    {
        Bhp::factory()->count(100)->create();
        $specificBhp = Bhp::factory()->create(['nama_bhp' => 'Special BHP 123']);

        $response = $this->getJson('/api/admin-pengelola/bhp?search=Special+BHP+123');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_bhp', $specificBhp->nama_bhp)
            ->assertJson([
                'pagination' => [
                    'total' => 1,
                ]
            ]);
    }

    // Test getBhpById with large dataset
    public function test_get_bhp_by_id_with_large_dataset()
    {
        Bhp::factory()->count(150)->create();
        $specificBhp = Bhp::factory()->create();

        $response = $this->getJson("/api/admin-pengelola/bhp/{$specificBhp->id}");
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $specificBhp->id,
                    'nama_bhp' => $specificBhp->nama_bhp,
                ]
            ]);
    }

    // Test mahasiswa pagination with more than 100 items
    public function test_get_mahasiswa_with_more_than_100_items()
    {
        User::factory()->count(150)->create(['status' => 1]);

        $response = $this->getJson('/api/admin-pengelola/mahasiswa');
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJson([
                'pagination' => [
                    'total' => 150,
                    'last_page' => 15,
                ]
            ]);

        // Test last page
        $response = $this->getJson('/api/admin-pengelola/mahasiswa?page=15&per_page=10');
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJson([
                'pagination' => [
                    'current_page' => 15,
                ]
            ]);
    }

    // Test dosen pagination with more than 100 items
    public function test_get_dosen_with_more_than_100_items()
    {
        Dosen::factory()->count(150)->create(['status' => 1]);

        $response = $this->getJson('/api/admin-pengelola/dosen');
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJson([
                'pagination' => [
                    'total' => 150,
                    'last_page' => 15,
                ]
            ]);
    }

    // Test getMahasiswaById with large dataset
    public function test_get_mahasiswa_by_id_with_large_dataset()
    {
        User::factory()->count(150)->create(['status' => 1]);
        $specificUser = User::factory()->create(['status' => 1]);

        $response = $this->getJson("/api/admin-pengelola/mahasiswa/{$specificUser->id}");
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $specificUser->id,
                    'name' => $specificUser->name,
                ]
            ]);
    }

    // Test getDosenById with large dataset
    public function test_get_dosen_by_id_with_large_dataset()
    {
        Dosen::factory()->count(150)->create(['status' => 1]);
        $specificDosen = Dosen::factory()->create(['status' => 1]);

        $response = $this->getJson("/api/admin-pengelola/dosen/{$specificDosen->id}");
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $specificDosen->id,
                    'name' => $specificDosen->name,
                ]
            ]);
    }

    // Test getInventaris with more than 100 items
    public function test_get_inventaris_with_more_than_100_items()
    {
        Inventaris::factory()->count(150)->create(['kondisi' => 'baik', 'status' => 1]);

        $response = $this->getJson('/api/admin-pengelola/inventaris');
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJson([
                'pagination' => [
                    'total' => 150,
                    'last_page' => 15,
                ]
            ]);
    }

    // Test search functionality across all endpoints with large datasets
    public function test_search_functionality_with_large_datasets()
    {
        // Create large datasets
        User::factory()->count(100)->create(['status' => 1]);
        $specificUser = User::factory()->create(['name' => 'Unique Student', 'status' => 1]);

        Dosen::factory()->count(100)->create(['status' => 1]);
        $specificDosen = Dosen::factory()->create(['name' => 'Unique Lecturer', 'status' => 1]);

        Bhp::factory()->count(100)->create();
        $specificBhp = Bhp::factory()->create(['nama_bhp' => 'Unique BHP']);

        Inventaris::factory()->count(100)->create(['kondisi' => 'baik', 'status' => 1]);
        $specificInventaris = Inventaris::factory()->create(['no_inventaris' => 'UNIQUE-123', 'kondisi' => 'baik', 'status' => 1]);

        // Test search for each endpoint
        $responses = [
            $this->getJson('/api/admin-pengelola/mahasiswa?search=Unique+Student'),
            $this->getJson('/api/admin-pengelola/dosen?search=Unique+Lecturer'),
            $this->getJson('/api/admin-pengelola/bhp?search=Unique+BHP'),
            $this->getJson('/api/admin-pengelola/inventaris?search=UNIQUE-123'),
        ];

        foreach ($responses as $response) {
            $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJson([
                    'pagination' => [
                        'total' => 1,
                    ]
                ]);
        }
    }
}