<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentRekomendasiLog extends Model
{
    protected $table = 'agent_rekomendasi_log';

    protected $fillable = [
        'prodi_id', 'indikator_id', 'judul_rekomendasi', 'deskripsi', 'prioritas', 'target_capai', 'deadline', 'status'
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function indikator()
    {
        return $this->belongsTo(IndikatorAkreditasi::class);
    }
}