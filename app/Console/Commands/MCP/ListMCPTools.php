<?php

namespace App\Console\Commands\MCP;

use App\Services\MCP\MCPClientService;
use Illuminate\Console\Command;

class ListMCPTools extends Command
{
    protected $signature = 'mcp:list-tools';

    protected $description = 'List all available MCP tools from the agents server';

    public function handle(MCPClientService $mcpClient): int
    {
        $this->info('Checking MCP server health...');

        $health = $mcpClient->healthCheck();

        $this->table(
            ['Server', 'Status'],
            [
                ['Agents (port 8001)', $health['agents'] ? '✅ Online' : '❌ Offline'],
                ['RAG (port 5001)', $health['rag'] ? '✅ Online' : '❌ Offline'],
            ]
        );

        if (! $health['agents']) {
            $this->error('Agents server is offline. Cannot list tools.');

            return Command::FAILURE;
        }

        $this->info("\nFetching available MCP tools...");

        $tools = $mcpClient->listTools();

        if (empty($tools)) {
            $this->warn('No tools found or failed to fetch tools.');

            return Command::FAILURE;
        }

        $this->table(
            ['Tool Name', 'Description'],
            collect($tools)->map(fn ($tool) => [
                $tool['name'],
                $tool['description'] ?? 'N/A',
            ])->toArray()
        );

        $this->info("\nTotal: ".count($tools).' tools available');

        return Command::SUCCESS;
    }
}
