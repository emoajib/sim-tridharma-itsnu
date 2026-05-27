<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedOnce();
    }

    public function test_super_admin_can_access_all_routes(): void
    {
        $admin = User::where('email', 'admin@itsnu.ac.id')->first();
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_dosen_cannot_access_admin_routes(): void
    {
        $dosen = User::where('email', 'dosen@itsnu.ac.id')->first();
        $response = $this->actingAs($dosen)->get(route('master-data.prodi'));
        $response->assertStatus(403);
    }

    public function test_kaprodi_can_access_master_data(): void
    {
        $kaprodi = User::where('email', 'kaprodi@itsnu.ac.id')->first();
        $response = $this->actingAs($kaprodi)->get(route('master-data.prodi'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_passes_through(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(302);
    }

    public function test_route_without_name_passes_through(): void
    {
        Route::get('/test-no-name', function () {
            return 'ok';
        });

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/test-no-name');
        $response->assertStatus(200);
    }

    public function test_ai_agent_routes_map_to_agent_ai_module(): void
    {
        $lpm = User::where('email', 'kaprodi@itsnu.ac.id')->first();
        $response = $this->actingAs($lpm)->get(route('peringatan'));
        $response->assertStatus(200);
    }
}
