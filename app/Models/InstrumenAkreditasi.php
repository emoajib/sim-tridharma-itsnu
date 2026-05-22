<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstrumenAkreditasi extends Model
{
    use HasFactory;

    protected $table = 'm_instrumen_akreditasi';

    protected $fillable = ['lembaga_id', 'nama_instrumen', 'matriks_kriteria'];

    protected $casts = [
        'matriks_kriteria' => 'json',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(LembagaAkreditasi::class, 'lembaga_id');
    }
}
