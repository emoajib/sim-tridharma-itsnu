<?php

namespace Tests\Feature\Http;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\SeedRolePermission;
use Tests\TestCase;

class AgentControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_unauthenticated_user_cannot_run_agent(): void
    {
        $response = $this->postJson('/api/agents/prediksi/run', []);
        $response->assertStatus(401);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        // Create a prodi to satisfy validation rules
        $prodi = Prodi::factory()->create();
        // Create a user WITHOUT any roles/permissions (just basic user)
        $user = User::factory()->create(['is_active' => true]);

        // Mock MCP calls to prevent actual HTTP requests
        Http::fake([
            '*/mcp/tools/call' => Http::response([
                'status' => 'completed',
                'result' => ['skor' => 85.5],
            ], 200),
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/agents/prediksi/run', [
            'prodi_id' => $prodi->id,
        ]);

        // User with no permissions should get 403 when trying to access agent-ai.trigger
        $response->assertStatus(403);
    }

    public function test_invalid_agent_name_returns_400(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/api/agents/invalid-agent/run', []);
        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid agent name']);
    }

    public function test_run_prediksi_agent_success(): void
    {
        $prodi = Prodi::factory()->create();

        Http::fake([
            '*/mcp/tools/call' => Http::response(['result' => ['skor' => 85.5]], 200),
        ]);

        $response = $this->actingAs($this->admin())->postJson('/api/agents/prediksi/run', [
            'prodi_id' => $prodi->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'completed',
            'result' => [
                'result' => ['skor' => 85.5],
            ],
        ]);
    }

    public function test_status_returns_operational(): void
    {
        $response = $this->actingAs($this->admin())->getJson('/api/agents/status');
        $response->assertStatus(200);
        $response->assertJson(['status' => 'operational']);
    }

    public function test_log_internal_creates_execution_log(): void
    {
        $payload = [
            'agent_name' => 'prediksi',
            'status' => 'success',
            'started_at' => '2026-01-01T00:00:00',
            'finished_at' => '2026-01-01T00:00:01',
            'duration_ms' => 1000,
            'input_data' => ['prodi_id' => 1],
            'output_data' => ['skor' => 85.5],
            'triggered_by' => 'test',
        ];

        $response = $this->actingAs($this->admin())->postJson('/api/internal/agents/log', $payload);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('agent_execution_log', [
            'agent_name' => 'prediksi',
            'status' => 'success',
            'duration_ms' => 1000,
            'triggered_by' => 'test',
        ]);
    }

    public function test_log_invalid_status_returns_422(): void
    {
        $payload = [
            'agent_name' => 'test',
            'status' => 'invalid_status',
            'started_at' => '2026-01-01T00:00:00',
            'finished_at' => '2026-01-01T00:00:01',
        ];

        $response = $this->actingAs($this->admin())->postJson('/api/internal/agents/log', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_log_internal_requires_agent_name(): void
    {
        $payload = [
            'status' => 'success',
            'started_at' => '2026-01-01T00:00:00',
            'finished_at' => '2026-01-01T00:00:01',
        ];

        $response = $this->actingAs($this->admin())->postJson('/api/internal/agents/log', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('agent_name');
    }
}
