<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentExecutionLog extends Model
{
    use HasFactory;

    protected $table = 'agent_execution_log';

    protected $fillable = [
        'agent_name', 'status', 'started_at', 'finished_at', 'duration_ms',
        'input_data', 'output_data', 'error_message', 'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'input_data' => 'array',
            'output_data' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
    }
}
