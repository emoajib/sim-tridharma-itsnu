<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_cannot_access_admin_users(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Users/Index'));
    }

    public function test_admin_can_create_user(): void
    {
        $role = Role::where('name', 'Dosen')->firstOrFail();
        $prodi = \App\Models\Prodi::factory()->create();
        $dosen = \App\Models\Dosen::factory()->create(['prodi_id' => $prodi->id]);

        $response = $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'Test User',
            'email' => 'testuser@itsnu.ac.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'role_ids' => [$role->id],
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@itsnu.ac.id',
            'name' => 'Test User',
            'dosen_id' => $dosen->id,
        ]);
        $user = User::where('email', 'testuser@itsnu.ac.id')->firstOrFail();
        $this->assertTrue($user->hasRole('Dosen'));
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $role = Role::where('name', 'Kaprodi')->firstOrFail();
        $prodi = \App\Models\Prodi::factory()->create();

        $response = $this->actingAs($this->admin())->put(route('admin.users.update', $user), [
            'name' => 'New Name',
            'email' => $user->email,
            'role_ids' => [$role->id],
            'prodi_id' => $prodi->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name', 'prodi_id' => $prodi->id]);
        $user->refresh();
        $this->assertTrue($user->hasRole('Kaprodi'));
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin())->delete(route('admin.users.destroy', $user));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_cannot_delete_super_admin(): void
    {
        $superAdmin = $this->admin();

        $response = $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $superAdmin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_admin_can_sync_roles(): void
    {
        $user = User::factory()->create();
        $roles = Role::whereIn('name', ['Dosen', 'Kaprodi'])->get();

        $response = $this->actingAs($this->admin())->postJson(route('admin.users.sync-roles', $user), [
            'role_ids' => $roles->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertTrue($user->hasRole('Dosen'));
        $this->assertTrue($user->hasRole('Kaprodi'));
    }
}
