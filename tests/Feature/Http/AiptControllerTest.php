<?php

namespace Tests\Feature\Http;

use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\SpmiCycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class AiptControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_index(): void
    {
        $response = $this->get(route('aipt.index'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('aipt.index'));
        $response->assertStatus(403);
    }

    public function test_index_returns_200_for_authorized_user(): void
    {
        Prodi::factory()->create();
        PeriodeAkademik::factory()->create();
        SpmiCycle::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('aipt.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Aipt/Index'));
    }
}
