<?php

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class ReconciliationControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('reconciliation.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('reconciliation.index'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('reconciliation.index'))
            ->assertStatus(200);
    }

    public function test_stats_returns_json(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->get(route('reconciliation.stats'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['total', 'pending', 'approved', 'rejected']);
    }

    public function test_approve_redirects_with_success(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('reconciliation.approve', 1));

        $response->assertSessionHas('success');
        $response->assertRedirect(route('reconciliation.index'));
    }

    public function test_batch_approve_redirects_with_success(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('reconciliation.batch-approve'), ['ids' => [1, 2, 3]]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('reconciliation.index'));
    }

    public function test_batch_approve_validates_ids(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('reconciliation.batch-approve'), []);

        $response->assertSessionHasErrors('ids');
    }

    public function test_reject_redirects_with_success(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('reconciliation.reject', 1));

        $response->assertSessionHas('success');
        $response->assertRedirect(route('reconciliation.index'));
    }
}
