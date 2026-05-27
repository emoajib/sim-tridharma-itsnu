<?php

namespace Tests\Feature\MCP;

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

    // ─── HEALTH CHECK ─────────────────────────────────────────

    public function test_health_check_returns_both_services(): void
    {
        Http::fake([
            'localhost:8001/health' => Http::response(['status' => 'ok'], 200),
            'localhost:5001/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $health = $this->service->healthCheck();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('agents', $health);
        $this->assertArrayHasKey('rag', $health);
        $this->assertTrue($health['agents']);
        $this->assertTrue($health['rag']);
    }

    public function test_health_check_returns_false_when_agents_down(): void
    {
        Http::fake([
            'localhost:8001/health' => Http::response(null, 500),
            'localhost:5001/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $health = $this->service->healthCheck();

        $this->assertFalse($health['agents']);
        $this->assertTrue($health['rag']);
    }

    public function test_health_check_returns_false_when_both_down(): void
    {
        Http::fake(function ($request) {
            throw new \Exception('Connection refused');
        });

        $health = $this->service->healthCheck();

        $this->assertFalse($health['agents']);
        $this->assertFalse($health['rag']);
    }

    // ─── CALL TOOL ────────────────────────────────────────────

    public function test_call_tool_returns_result_on_success(): void
    {
        Http::fake([
            'localhost:8001/mcp/tools/call' => Http::response(['result' => 'ok'], 200),
        ]);

        $result = $this->service->callTool('test_tool', ['key' => 'value']);

        $this->assertEquals(['result' => 'ok'], $result);
    }

    public function test_call_tool_handles_timeout_gracefully(): void
    {
        Http::fake([
            'localhost:8001/mcp/tools/call' => Http::response(null, 400),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('MCP tool call failed: 400');

        $this->service->callTool('test_tool');
    }

    public function test_call_tool_throws_on_connection_error(): void
    {
        Http::fake(function () {
            throw new \Exception('cURL error 28: Connection timed out');
        });

        $this->expectException(\Exception::class);

        $this->service->callTool('test_tool');
    }

    // ─── API KEY HEADERS ──────────────────────────────────────

    public function test_api_key_is_sent_in_headers(): void
    {
        Http::fake(function ($request) {
            $this->assertTrue($request->hasHeader('X-API-Key'));
            $this->assertEquals('', $request->header('X-API-Key')[0]);

            return Http::response(['tools' => ['tool1']], 200);
        });

        $this->service->listTools();
    }

    public function test_content_type_is_json_for_tool_calls(): void
    {
        Http::fake(function ($request) {
            $this->assertEquals('application/json', $request->header('Content-Type')[0]);

            return Http::response(['result' => 'ok'], 200);
        });

        $this->service->callTool('test_tool');
    }

    public function test_accept_header_is_set(): void
    {
        Http::fake(function ($request) {
            $this->assertEquals('application/json', $request->header('Accept')[0]);

            return Http::response(['tools' => []], 200);
        });

        $this->service->listTools();
    }

    // ─── LIST TOOLS ───────────────────────────────────────────

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
}
