<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class RoleSwitchControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_switch(): void
    {
        $response = $this->post(route('role.switch'), ['role' => 'admin']);
        $response->assertStatus(302);
    }

    public function test_switch_to_valid_role_redirects(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->from(route('dashboard'))
            ->post(route('role.switch'), ['role' => 'Super Admin']);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_switch_to_invalid_role_fails(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('role.switch'), ['role' => 'superadmin']);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    public function test_switch_requires_role_field(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->from(route('dashboard'))
            ->post(route('role.switch'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['role']);
    }
}
