<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndikatorAkreditasi extends Model
{
    use SoftDeletes;

    protected $table = 'm_indikator_akreditasi';

    protected $fillable = [
        'kode_indikator', 'nama_indikator', 'kriteria', 'bobot', 'target', 'jenis_akreditasi', 'is_active', 'instrumen_id'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'bobot' => 'decimal:2',
        ];
    }

    public function instrumen()
    {
        return $this->belongsTo(\App\Models\InstrumenAkreditasi::class, 'instrumen_id');
    }
}
