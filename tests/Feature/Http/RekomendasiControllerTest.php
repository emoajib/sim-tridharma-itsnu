<?php

namespace Tests\Feature\Http;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class RekomendasiControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_index(): void
    {
        $response = $this->get(route('rekomendasi'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirected_from_run(): void
    {
        $response = $this->post(route('rekomendasi.run'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403_on_index(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('rekomendasi'));
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_gets_403_on_run(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->post(route('rekomendasi.run'));
        $response->assertStatus(403);
    }

    public function test_index_returns_200_for_authorized_user(): void
    {
        Prodi::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('rekomendasi'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Agent/Rekomendasi/Index'));
    }

    public function test_run_redirects_for_authorized_user(): void
    {
        Prodi::factory()->create();

        Http::fake([
            '*/mcp/*' => Http::response(['recommendation_count' => 5], 200),
        ]);

        $response = $this->actingAs($this->admin())
            ->from(route('rekomendasi'))
            ->post(route('rekomendasi.run'));

        $response->assertStatus(302);
    }
}
