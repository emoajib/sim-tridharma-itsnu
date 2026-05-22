<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;

class ProdiKeahlian extends Model
{
    use HasActiveScope;

    protected $table = 'm_prodi_keahlian';

    protected $fillable = [
        'prodi_id', 'nama_keahlian', 'deskripsi', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function dosens()
    {
        return $this->belongsToMany(Dosen::class, 'trx_prodi_keahlian_dosen', 'prodi_keahlian_id', 'dosen_id')
            ->withPivot('is_utama')
            ->withTimestamps();
    }
}
