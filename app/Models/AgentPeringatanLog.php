<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentPeringatanLog extends Model
{
    use HasFactory;

    protected $table = 'agent_peringatan_log';

    protected $fillable = [
        'prodi_id', 'dosen_id', 'jenis_peringatan', 'tingkat',
        'pesan', 'is_read', 'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'dibaca_pada' => 'datetime',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }
}
