<?php

namespace Tests\Feature\Http;

use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\Feature\SeedRolePermission;
use Tests\TestCase;

class PrediksiControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_unauthenticated_user_redirected_from_index(): void
    {
        $response = $this->get(route('prediksi'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('prediksi'));
        $response->assertStatus(403);
    }

    public function test_index_shows_form_for_authorized_user(): void
    {
        Prodi::factory()->create();
        PeriodeAkademik::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('prediksi'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Prediksi/Index'));
    }

    public function test_run_calls_prediksi_skor_agent(): void
    {
        Prodi::factory()->create();
        PeriodeAkademik::factory()->create();

        Http::fake([
            '*/mcp/tools/call' => Http::response(['task_id' => 'test-123'], 200),
            '*/mcp/tasks/test-123' => Http::response([
                'status' => 'completed',
                'result' => [
                    'skor_prediksi' => 85.5,
                    'probabilitas' => ['unggul' => 0.8, 'baik_sekali' => 0.15, 'baik' => 0.05],
                    'confidence_interval' => 4.5,
                    'confidence_interval_details' => [
                        'lower' => 83.25,
                        'upper' => 87.75,
                    ],
                    'trend_analysis' => 'Positif',
                    'historical_data_points' => 3,
                    'budget_analysis' => 'netral',
                    'method' => 'monte_carlo',
                    'mc_samples' => 1000,
                    'mc_mean' => 85.0,
                    'mc_std' => 2.3,
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin())->from(route('prediksi'))->post(route('prediksi.run'), [
            'prodi_id' => 1,
            'periode_id' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('prediksi'));

        // Get session data - check for direct flash messages in session
        $sessionData = session()->all();

        // Debug output
        Log::debug('Session data in test: '.print_r($sessionData, true));

        // Check if we have error message directly in session
        if (isset($sessionData['error']) && ! empty($sessionData['error'])) {
            $this->fail('Session has error: '.$sessionData['error']);
        }

        // Check for success message directly in session
        $this->assertTrue(isset($sessionData['success']) && ! empty($sessionData['success']), 'Expected success flash message not found. Session data: '.print_r($sessionData, true));
    }

    public function test_latest_returns_empty_when_no_data(): void
    {
        $response = $this->actingAs($this->admin())->getJson(route('prediksi.latest'));
        $response->assertStatus(200);
        $response->assertJson([]);
    }
}
