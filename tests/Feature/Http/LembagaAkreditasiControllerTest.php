<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class LembagaAkreditasiControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('admin.lembaga.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Asesor Tamu');

        $this->actingAs($user)
            ->get(route('admin.lembaga.index'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('admin.lembaga.index'))
            ->assertStatus(200);
    }
}
