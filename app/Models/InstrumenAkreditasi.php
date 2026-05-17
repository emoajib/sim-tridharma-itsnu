<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrumenAkreditasi extends Model
{
    protected $table = 'm_instrumen_akreditasi';

    protected $fillable = ['lembaga_id', 'nama_instrumen', 'matriks_kriteria'];

    protected $casts = [
        'matriks_kriteria' => 'json',
    ];

    public function lembaga()
    {
        return $this->belongsTo(LembagaAkreditasi::class, 'lembaga_id');
    }
}
