<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrasiGoogleScholar extends Model
{
    protected $table = 'integrasi_google_scholar';

    protected $fillable = [
        'dosen_id',
        'google_scholar_id',
        'judul',
        'penulis',
        'jurnal',
        'tahun',
        'doi',
        'url',
        'sitasi',
        'is_verified',
        'sinkron_pada',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'sitasi' => 'integer',
        'is_verified' => 'boolean',
        'sinkron_pada' => 'datetime',
    ];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}
