<?php

namespace Tests\Feature\Http;

use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class PeringatanControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_index(): void
    {
        $response = $this->get(route('peringatan'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('peringatan'));
        $response->assertStatus(403);
    }

    public function test_index_shows_warnings_for_authorized_user(): void
    {
        Prodi::factory()->create();
        PeriodeAkademik::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('peringatan'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Peringatan/Index'));
    }

    public function test_run_calls_peringatan_check_agent(): void
    {
        Prodi::factory()->create();

        Http::fake([
            '*/mcp/tools/call' => Http::response(['task_id' => 'test-123'], 200),
            '*/mcp/tasks/test-123' => Http::response([
                'status' => 'completed',
                'result' => [
                    'warnings' => [
                        [
                            'level' => 'warning',
                            'kategori' => 'rkat',
                            'judul' => 'Test Warning',
                            'deskripsi' => 'This is a test warning',
                            'dosen_id' => null,
                        ],
                    ],
                    'total' => 1,
                    'prodi_id' => 1,
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin())->from(route('peringatan'))->post(route('peringatan.run'), [
            'prodi_id' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('peringatan'));

        // Get session data
        $sessionData = session()->all();

        // Check for error first
        if (isset($sessionData['error']) && ! empty($sessionData['error'])) {
            $this->fail('Session has error: '.$sessionData['error']);
        }

        $this->assertTrue(isset($sessionData['success']) && ! empty($sessionData['success']), 'Expected success flash message not found.');
    }
}
