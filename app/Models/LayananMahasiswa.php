<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananMahasiswa extends Model
{
    protected $table = 'trx_layanan_mahasiswa';

    protected $fillable = [
        'periode_id',
        'jenis_layanan',
        'nama_program',
        'tanggal_pelaksanaan',
        'jumlah_peserta',
        'file_surat_tugas',
        'file_laporan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelaksanaan' => 'date',
        ];
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
