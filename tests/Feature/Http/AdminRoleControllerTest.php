<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class AdminRoleControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_cannot_access_admin_roles(): void
    {
        $response = $this->get(route('admin.roles.index'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('admin.roles.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_roles_index(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.roles.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Roles/Index'));
    }

    public function test_admin_can_create_role(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.roles.store'), [
            'name' => 'Test Role',
            'guard_name' => 'web',
            'permission_ids' => [],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('roles', ['name' => 'Test Role']);
    }

    public function test_admin_can_update_role(): void
    {
        $role = Role::where('name', 'Dosen')->firstOrFail();

        $response = $this->actingAs($this->admin())->put(route('admin.roles.update', $role), [
            'name' => 'Dosen Updated',
            'permission_ids' => [],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Dosen Updated']);
    }

    public function test_admin_can_sync_permissions(): void
    {
        $role = Role::where('name', 'Dosen')->firstOrFail();
        $permissions = Permission::where('name', 'like', 'master-data.%')->get();

        $response = $this->actingAs($this->admin())->postJson(route('admin.roles.sync-permissions', $role), [
            'permission_ids' => $permissions->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertTrue($role->hasPermissionTo('master-data.view'));
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $role = Role::where('name', 'Super Admin')->firstOrFail();

        $response = $this->actingAs($this->admin())->delete(route('admin.roles.destroy', $role));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_admin_can_delete_role(): void
    {
        $role = Role::create(['name' => 'Temp Role', 'guard_name' => 'web']);

        $response = $this->actingAs($this->admin())->delete(route('admin.roles.destroy', $role));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
