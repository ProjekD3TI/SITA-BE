<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTaRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ta_routes() {
        $adminTA = Admin::factory()->create(['role' => 'admin_ta']);
    
        Sanctum::actingAs($adminTA, ['role:admin_ta']);
    
        $response = $this->getJson('/api/admin-ta/dashboard');
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Welcome to Admin TA Dashboard']);
    }
}