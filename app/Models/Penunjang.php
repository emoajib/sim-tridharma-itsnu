<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penunjang extends Model
{
    use SoftDeletes;

    protected $table = 'trx_penunjang';

    protected $fillable = [
        'dosen_id', 'prodi_id', 'periode_id', 'nama_kegiatan', 'jenis_kegiatan',
        'tingkat', 'peran', 'tahun', 'is_verified'
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
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
