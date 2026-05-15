<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentVerifikasiHasil extends Model
{
    protected $table = 'agent_verifikasi_hasil';

    protected $fillable = [
        'prodi_id', 'dosen_id', 'doc_bukti_id', 'indikator_id',
        'status', 'catatan', 'tingkat_kepercayaan'
    ];

    protected function casts(): array
    {
        return [
            'tingkat_kepercayaan' => 'decimal:2',
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

    public function dokumen()
    {
        return $this->belongsTo(DokumenBukti::class, 'doc_bukti_id');
    }

    public function indikator()
    {
        return $this->belongsTo(IndikatorAkreditasi::class, 'indikator_id');
    }
}
