<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminPengelolaRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pengelola_routes() {
        $adminPengelola = Admin::factory()->adminPengelola()->create();

        Sanctum::actingAs($adminPengelola, ['role:admin_pengelola']);

        $response = $this->getJson('/api/admin-pengelola/dashboard');
        $response->assertStatus(200)
                ->assertJson(['message' => 'Welcome to Admin Pengelola Dashboard']);
    }

    public function test_admin_pengelola_routes_with_invalid_role() {
        $adminTA = Admin::factory()->create(['role' => 'admin_ta']);

        Sanctum::actingAs($adminTA, ['role:admin_ta']);

        $response = $this->getJson('/api/admin-pengelola/dashboard');
        $response->assertStatus(403); 
    }
}