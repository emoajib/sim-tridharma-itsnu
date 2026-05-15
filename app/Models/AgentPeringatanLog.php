<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPeringatanLog extends Model
{
    protected $table = 'agent_peringatan_log';

    protected $fillable = [
        'prodi_id', 'dosen_id', 'jenis_peringatan', 'tingkat',
        'pesan', 'is_read', 'read_at'
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }
}
