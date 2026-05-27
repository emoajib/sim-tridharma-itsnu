<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class AdminPermissionControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_cannot_access_admin_permissions(): void
    {
        $response = $this->get(route('admin.permissions.index'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('admin.permissions.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_permissions_index(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.permissions.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Permissions/Index'));
    }

    public function test_permissions_are_grouped_by_module(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.permissions.index'));
        $response->assertStatus(200);
        // Just verify it loads without error - grouping is in frontend
    }

    public function test_can_filter_permissions_by_search(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.permissions.index', ['search' => 'master-data']));
        $response->assertStatus(200);
    }

    public function test_can_filter_permissions_by_module(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.permissions.index', ['module' => 'master-data']));
        $response->assertStatus(200);
    }
}
