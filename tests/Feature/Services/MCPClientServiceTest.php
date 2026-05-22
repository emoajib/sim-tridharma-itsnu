<?php

namespace Tests\Feature\Services;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MCPClientServiceTest extends TestCase
{
    private MCPClientService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MCPClientService;
    }

    public function test_list_tools_returns_tools_on_success(): void
    {
        Http::fake([
            'localhost:8001/api/mcp/tools' => Http::response(['tools' => ['tool1', 'tool2']], 200),
        ]);

        $tools = $this->service->listTools();

        $this->assertEquals(['tool1', 'tool2'], $tools);
    }

    public function test_list_tools_returns_empty_on_failure(): void
    {
        Http::fake([
            'localhost:8001/*' => Http::response(null, 500),
        ]);

        $tools = $this->service->listTools();

        $this->assertEmpty($tools);
    }

    public function test_list_tools_returns_empty_on_exception(): void
    {
        Http::fake(function () {
            throw new \Exception('Connection refused');
        });

        $tools = $this->service->listTools();

        $this->assertEmpty($tools);
    }

    public function test_call_tool_returns_result_on_success(): void
    {
        Http::fake([
            'localhost:8001/mcp/tools/call' => Http::response(['result' => 'ok'], 200),
        ]);

        $result = $this->service->callTool('test_tool', ['key' => 'value']);

        $this->assertEquals(['result' => 'ok'], $result);
    }

    public function test_call_tool_throws_on_failure(): void
    {
        Http::fake([
            'localhost:8001/mcp/tools/call' => Http::response(null, 400),
        ]);

        $this->expectException(\Exception::class);
        $this->service->callTool('test_tool');
    }

    public function test_health_check_returns_both_servers_when_online(): void
    {
        Http::fake([
            'localhost:8001/health' => Http::response(['status' => 'ok'], 200),
            'localhost:5001/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $health = $this->service->healthCheck();

        $this->assertTrue($health['agents']);
        $this->assertTrue($health['rag']);
    }

    public function test_health_check_returns_offline_when_connection_refused(): void
    {
        Http::fake(function ($request) {
            throw new \Exception('Connection refused');
        });

        $health = $this->service->healthCheck();

        $this->assertFalse($health['agents']);
        $this->assertFalse($health['rag']);
    }

    public function test_run_prediksi_skor_calls_correct_tool(): void
    {
        Http::fake([
            'localhost:8001/mcp/tools/call' => Http::response(['task_id' => 'abc-123'], 200),
            'localhost:8001/mcp/tasks/abc-123' => Http::response([
                'status' => 'completed',
                'result' => ['skor' => 85.5],
            ], 200),
        ]);

        $result = $this->service->runPrediksiSkor(1);

        $this->assertEquals(['skor' => 85.5], $result);
    }

    public function test_run_prediksi_skor_throws_on_timeout(): void
    {
        Http::fake([
            'localhost:8001/mcp/tools/call' => Http::response(['task_id' => 'abc-123'], 200),
            'localhost:8001/*' => Http::response(['status' => 'running'], 200),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('timeout');
        $this->service->runPrediksiSkor(1);
    }

    public function test_ask_rag_calls_rag_server(): void
    {
        Http::fake([
            'localhost:5001/mcp/tools/call' => Http::response(['task_id' => 'rag-123'], 200),
            'localhost:5001/mcp/tasks/rag-123' => Http::response([
                'status' => 'completed',
                'result' => ['answer' => 'Test answer'],
            ], 200),
        ]);

        $result = $this->service->askRAG('Apa itu akreditasi?');

        $this->assertEquals(['answer' => 'Test answer'], $result);
    }

    public function test_embed_text_calls_sync_tool(): void
    {
        Http::fake([
            'localhost:5001/mcp/tools/call' => Http::response(['embedding' => [0.1, 0.2, 0.3]], 200),
        ]);

        $result = $this->service->embedText('Test text');

        $this->assertEquals(['embedding' => [0.1, 0.2, 0.3]], $result);
    }
}
