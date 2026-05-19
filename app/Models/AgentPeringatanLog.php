<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPeringatanLog extends Model
{
    protected $table = 'agent_peringatan_log';

    protected $fillable = [
        'prodi_id', 'dosen_id', 'jenis_peringatan', 'tingkat',
        'pesan', 'is_read', 'dibaca_pada'
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'dibaca_pada' => 'datetime',
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
