<?php

namespace Tests\Feature\Http;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class VerifikasiControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_index(): void
    {
        $response = $this->get(route('verifikasi'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('verifikasi'));
        $response->assertStatus(403);
    }

    public function test_index_shows_verification_page_for_authorized_user(): void
    {
        Prodi::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('verifikasi'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Verifikasi/Index'));
    }

    public function test_run_calls_verifikasi_document_agent(): void
    {
        Prodi::factory()->create();

        Http::fake([
            '*/mcp/tools/call' => Http::response(['task_id' => 'test-123'], 200),
            '*/mcp/tasks/test-123' => Http::response([
                'status' => 'completed',
                'result' => [
                    'valid_count' => 5,
                    'need_review_count' => 2,
                    'invalid_count' => 1,
                    'total_checked' => 8,
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin())->from(route('verifikasi'))->post(route('verifikasi.run'), [
            'prodi_id' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('verifikasi'));

        // Get session data
        $sessionData = session()->all();

        // Check for error first
        if (isset($sessionData['error']) && ! empty($sessionData['error'])) {
            $this->fail('Session has error: '.$sessionData['error']);
        }

        $this->assertTrue(isset($sessionData['success']) && ! empty($sessionData['success']), 'Expected success flash message not found.');
    }
}
