<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentRekomendasiLog extends Model
{
    use HasFactory;

    protected $table = 'agent_rekomendasi_log';

    protected $fillable = [
        'prodi_id', 'indikator_id', 'judul_rekomendasi', 'deskripsi', 'prioritas', 'target_capai', 'deadline', 'status',
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IndikatorAkreditasi::class);
    }
}
