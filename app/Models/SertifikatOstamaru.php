<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SertifikatOstamaru extends Model
{
    protected $table = 'trx_sertifikat_ostamaru';

    protected $fillable = [
        'mahasiswa_id',
        'periode_id',
        'jenis_sertifikat',
        'nomor_sertifikat',
        'tanggal_terbit',
        'file_sertifikat',
        'is_downloadable',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
            'is_downloadable' => 'boolean',
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

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
