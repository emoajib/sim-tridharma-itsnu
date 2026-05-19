<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prodi extends Model
{
    use SoftDeletes;

    protected $table = 'm_prodi';

    protected $fillable = [
        'kode_prodi', 'nama_prodi', 'fakultas_id', 'jenjang',
        'akreditasi', 'sk_akreditasi', 'tanggal_kadaluarsa', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_kadaluarsa' => 'date',
        ];
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function lembaga()
    {
        return $this->belongsTo(LembagaAkreditasi::class, 'lembaga_akreditasi_id');
    }

    public function dosens()
    {
        return $this->hasMany(Dosen::class, 'prodi_id');
    }
}
