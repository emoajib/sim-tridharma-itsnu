<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bkd extends Model
{
    use SoftDeletes;

    protected $table = 'trx_bkd';

    protected $fillable = [
        'dosen_id', 'prodi_id', 'periode_id',
        'total_sks_mengajar', 'total_sks_penelitian', 'total_sks_pkm',
        'total_sks_penunjang', 'total_sks', 'status', 'catatan', 'is_verified'
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'total_sks_mengajar' => 'decimal:2',
            'total_sks_penelitian' => 'decimal:2',
            'total_sks_pkm' => 'decimal:2',
            'total_sks_penunjang' => 'decimal:2',
            'total_sks' => 'decimal:2',
        ];
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
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
