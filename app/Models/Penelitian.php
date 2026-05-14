<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penelitian extends Model
{
    use SoftDeletes;

    protected $table = 'trx_penelitian';

    protected $fillable = [
        'dosen_id', 'prodi_id', 'periode_id', 'judul_penelitian', 'jenis_penelitian',
        'sumber_dana', 'jumlah_dana', 'tahun_pelaksanaan', 'is_verified'
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'jumlah_dana' => 'decimal:2',
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
