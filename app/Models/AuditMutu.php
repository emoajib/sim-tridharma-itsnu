<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditMutu extends Model
{
    use SoftDeletes;

    protected $table = 'trx_audit_mutu';

    protected $fillable = [
        'prodi_id', 'periode_id', 'judul_audit', 'tanggal_audit', 'auditor',
        'temuan', 'rekomendasi', 'status', 'tindak_lanjut'
    ];

    protected function casts(): array
    {
        return [
            'tanggal_audit' => 'date',
        ];
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }
}
