<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KegiatanPendidikan extends Model
{
    use SoftDeletes;

    protected $table = 'trx_kegiatan_pendidikan';

    protected $fillable = [
        'dosen_id', 'prodi_id', 'periode_id', 'nama_kegiatan', 'jenis_kegiatan',
        'mata_kuliah_id', 'sks', 'jumlah_mahasiswa', 'jumlah_pertemuan', 'is_verified'
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'sks' => 'integer',
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

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}
