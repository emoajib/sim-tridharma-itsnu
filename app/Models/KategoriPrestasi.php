<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPrestasi extends Model
{
    use HasFactory;

    protected $table = 'm_kategori_prestasi';

    protected $fillable = [
        'nama_kategori',
        'jenis',
    ];

    public function prestasis()
    {
        return $this->hasMany(Prestasi::class, 'kategori_id');
    }
}
