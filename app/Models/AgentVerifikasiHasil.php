<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentVerifikasiHasil extends Model
{
    use HasFactory;

    protected $table = 'agent_verifikasi_hasil';

    protected $fillable = [
        'prodi_id', 'dosen_id', 'doc_bukti_id', 'indikator_id',
        'status', 'catatan', 'tingkat_kepercayaan',
    ];

    protected function casts(): array
    {
        return [
            'tingkat_kepercayaan' => 'decimal:2',
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

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(DokumenBukti::class, 'doc_bukti_id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IndikatorAkreditasi::class, 'indikator_id');
    }
}
