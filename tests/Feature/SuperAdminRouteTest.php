<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuperAdminRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_routes() {
        $superadmin = Admin::factory()->superadmin()->create();

        Sanctum::actingAs($superadmin, ['role:superadmin']);

        $response = $this->getJson('/api/superadmin/dashboard');
        $response->assertStatus(200)
                ->assertJson(['message' => 'Welcome to Superadmin Dashboard']);
    }
}