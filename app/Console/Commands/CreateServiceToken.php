<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateServiceToken extends Command
{
    protected $signature = 'service:create-token {--name=ai-service : Service name} {--email= : Service email}';

    protected $description = 'Create a Sanctum API token for internal microservice';

    public function handle()
    {
        $name = $this->option('name');
        $email = $this->option('email') ?? 'ai-service@internal.local';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]
        );

        $token = $user->createToken($name, ['server:internal'])->plainTextToken;

        $this->info('Service token created successfully!');
        $this->line('');
        $this->table(
            ['Service', 'Email', 'Token'],
            [[$name, $email, $token]]
        );
        $this->line('');
        $this->warn('⚠️  Store this token securely. It will not be shown again.');
        $this->line('');
        $this->info('Usage in AI service:');
        $this->line('  curl -X POST http://localhost/api/internal/agents/log \\');
        $this->line("    -H 'Authorization: Bearer {$token}' \\");
        $this->line("    -H 'Content-Type: application/json' \\");
        $this->line("    -d '{\"agent_name\":\"test\",\"status\":\"success\",\"started_at\":\"2026-01-01T00:00:00\",\"finished_at\":\"2026-01-01T00:00:01\",\"duration_ms\":1000}'");

        return Command::SUCCESS;
    }
}
