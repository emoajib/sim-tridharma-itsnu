<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publikasi extends Model
{
    use SoftDeletes;

    protected $table = 'trx_publikasi';

    protected $fillable = [
        'dosen_id', 'prodi_id', 'periode_id', 'judul_publikasi', 'jenis_publikasi',
        'tingkat', 'link', 'tahun', 'is_verified'
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
