<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sarana extends Model
{
    use HasActiveScope, SoftDeletes;

    protected $table = 'm_sarana';

    protected $fillable = [
        'prodi_id', 'nama_sarana', 'jenis_sarana', 'jumlah', 'kondisi',
        'tanggal_kalibrasi', 'tanggal_kalibrasi_berikut', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'jumlah' => 'integer',
            'tanggal_kalibrasi' => 'date',
            'tanggal_kalibrasi_berikut' => 'date',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }
}
