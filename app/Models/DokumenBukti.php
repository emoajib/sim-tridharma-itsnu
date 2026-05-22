<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DokumenBukti extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'doc_bukti';

    protected $fillable = [
        'nama_dokumen', 'file_path', 'file_type', 'file_size', 'hash',
        'dosen_id', 'prodi_id', 'keterangan',
        'documentable_type', 'documentable_id',
    ];

    protected $guarded = ['is_verified'];

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

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }
}
