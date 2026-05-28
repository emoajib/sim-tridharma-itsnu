<?php

namespace Tests\Feature\Http;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class IntegrasiControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_index(): void
    {
        $response = $this->get(route('integrasi'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirected_from_run(): void
    {
        $response = $this->post(route('integrasi.run'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirected_from_sync(): void
    {
        $response = $this->post(route('integrasi.sync'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403_on_index(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('integrasi'));
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_gets_403_on_run(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->post(route('integrasi.run'));
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_gets_403_on_sync(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->post(route('integrasi.sync'));
        $response->assertStatus(403);
    }

    public function test_index_returns_200_for_authorized_user(): void
    {
        Prodi::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('integrasi'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Agent/Integrasi/Index'));
    }

    public function test_run_redirects_for_authorized_user(): void
    {
        Prodi::factory()->create();

        Http::fake([
            '*/mcp/*' => Http::response(['records_pulled' => 10, 'conflicts_detected' => 2], 200),
        ]);

        $response = $this->actingAs($this->admin())
            ->from(route('integrasi'))
            ->post(route('integrasi.run'));

        $response->assertStatus(302);
    }

    public function test_sync_redirects_for_authorized_user(): void
    {
        Prodi::factory()->create();

        Http::fake([
            '*/mcp/*' => Http::response(['records_pulled' => 5, 'conflicts_detected' => 0], 200),
        ]);

        $response = $this->actingAs($this->admin())
            ->from(route('integrasi'))
            ->post(route('integrasi.sync'));

        $response->assertStatus(302);
    }
}
