<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Skpi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_skpi';

    protected $fillable = [
        'mahasiswa_id',
        'periode_id',
        'jenis_kegiatan',
        'nama_kegiatan',
        'tingkat',
        'peran',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_kompen',
        'poin_skpi',
        'file_sertifikat',
        'status_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
