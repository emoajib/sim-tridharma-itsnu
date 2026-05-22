<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkatPagu extends Model
{
    use HasFactory;

    protected $table = 'trx_rkat_pagu';

    protected $fillable = [
        'periode_id',
        'unit_type',
        'unit_id',
        'pagu_total',
        'terpakai',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }

    public function unit()
    {
        if ($this->unit_type === 'Fakultas') {
            return $this->belongsTo(Fakultas::class, 'unit_id');
        } elseif ($this->unit_type === 'Prodi') {
            return $this->belongsTo(Prodi::class, 'unit_id');
        }

        return null;
    }
}
