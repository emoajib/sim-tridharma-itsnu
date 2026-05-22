<?php

namespace Tests\Feature\Services;

use App\Models\AgentExecutionLog;
use App\Models\AgentGeneratorHistory;
use App\Models\AgentPeringatanLog;
use App\Models\AgentPredictionHistory;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Services\Agent\AgentOrchestrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentOrchestrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AgentOrchestrationService $service;

    private Fakultas $fakultas;

    private Prodi $prodi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AgentOrchestrationService;
        $this->fakultas = Fakultas::create([
            'kode_fakultas' => 'FTI', 'nama_fakultas' => 'Teknik',
        ]);
        $this->prodi = Prodi::create([
            'kode_prodi' => 'IF', 'nama_prodi' => 'Informatika',
            'fakultas_id' => $this->fakultas->id, 'jenjang' => 'S1',
        ]);
    }

    public function test_get_allowed_agents_returns_six_agents(): void
    {
        $agents = $this->service->getAllowedAgents();

        $this->assertCount(6, $agents);
        $this->assertContains('verifikasi', $agents);
        $this->assertContains('prediksi', $agents);
        $this->assertContains('rekomendasi', $agents);
        $this->assertContains('peringatan', $agents);
        $this->assertContains('generator', $agents);
        $this->assertContains('integrasi', $agents);
    }

    public function test_is_valid_agent_returns_true_for_allowed(): void
    {
        $this->assertTrue($this->service->isValidAgent('verifikasi'));
        $this->assertTrue($this->service->isValidAgent('prediksi'));
    }

    public function test_is_valid_agent_returns_false_for_unknown(): void
    {
        $this->assertFalse($this->service->isValidAgent('hacker'));
        $this->assertFalse($this->service->isValidAgent(''));
    }

    public function test_log_execution_creates_record(): void
    {
        $log = $this->service->logExecution([
            'agent_name' => 'verifikasi',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $this->assertNotNull($log->id);
        $this->assertEquals('verifikasi', $log->agent_name);
        $this->assertEquals('running', $log->status);
    }

    public function test_get_status_returns_idle_when_no_logs(): void
    {
        $status = $this->service->getStatus();

        $this->assertArrayHasKey('agents', $status);
        $this->assertArrayHasKey('recent_logs', $status);
        $this->assertEquals('idle', $status['agents']['verifikasi']['status']);
        $this->assertNull($status['agents']['verifikasi']['last_run']);
    }

    public function test_get_status_shows_latest_run(): void
    {
        AgentExecutionLog::create([
            'agent_name' => 'prediksi',
            'status' => 'success',
            'prodi_id' => $this->prodi->id,
            'started_at' => now()->subHour(),
            'finished_at' => now(),
        ]);

        $status = $this->service->getStatus();

        $this->assertEquals('success', $status['agents']['prediksi']['status']);
        $this->assertNotNull($status['agents']['prediksi']['last_run']);
    }

    public function test_get_latest_results_returns_empty_when_no_data(): void
    {
        $results = $this->service->getLatestResults();

        $this->assertEmpty($results['logs']);
        $this->assertEmpty($results['predictions']);
        $this->assertEmpty($results['warnings']);
        $this->assertEmpty($results['generations']);
    }

    public function test_get_latest_results_limits_to_10(): void
    {
        for ($i = 0; $i < 15; $i++) {
            AgentExecutionLog::create([
                'agent_name' => 'verifikasi', 'status' => 'success',
                'started_at' => now(), 'finished_at' => now(),
            ]);
        }

        $results = $this->service->getLatestResults();

        $this->assertCount(10, $results['logs']);
    }

    public function test_get_latest_results_returns_related_data(): void
    {
        AgentExecutionLog::create([
            'agent_name' => 'verifikasi', 'status' => 'success',
            'prodi_id' => $this->prodi->id, 'started_at' => now(), 'finished_at' => now(),
        ]);
        AgentPredictionHistory::create([
            'prodi_id' => $this->prodi->id, 'skor_prediksi' => 85.5,
            'created_at' => now(),
        ]);
        AgentPeringatanLog::create([
            'agent' => 'verifikasi', 'prodi_id' => $this->prodi->id,
            'tingkat' => 'warning', 'jenis_peringatan' => 'test', 'pesan' => 'test',
            'is_read' => false,
        ]);
        AgentGeneratorHistory::create([
            'prodi_id' => $this->prodi->id, 'jenis_dokumen' => 'LED',
            'judul' => 'LED Test', 'status' => 'completed',
            'generated_by' => 'system', 'created_at' => now(),
        ]);

        $results = $this->service->getLatestResults();

        $this->assertCount(1, $results['logs']);
        $this->assertCount(1, $results['predictions']);
        $this->assertCount(1, $results['warnings']);
        $this->assertCount(1, $results['generations']);
    }
}
