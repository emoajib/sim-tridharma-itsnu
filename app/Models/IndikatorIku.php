<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndikatorIku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_indikator_iku';

    protected $fillable = [
        'kode_iku',
        'nama_iku',
        'deskripsi',
        'satuan',
        'bobot',
    ];

    public function cascadingTarget(): HasMany
    {
        return $this->hasMany(CascadingIku::class, 'iku_id');
    }

    public function usulanRkat(): HasMany
    {
        return $this->hasMany(UsulanRkat::class, 'iku_id');
    }
}
