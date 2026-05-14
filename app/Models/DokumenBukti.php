<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DokumenBukti extends Model
{
    use SoftDeletes;

    protected $table = 'doc_bukti';

    protected $fillable = [
        'dosen_id', 'prodi_id', 'nama_dokumen', 'file_path', 'file_type',
        'file_size', 'hash', 'keterangan',
        'documentable_type', 'documentable_id', 'is_verified'
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}
