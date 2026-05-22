<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdiKeahlianDosen extends Model
{
    use HasFactory;

    protected $table = 'trx_prodi_keahlian_dosen';

    protected $fillable = [
        'dosen_id', 'prodi_keahlian_id', 'is_utama',
    ];

    protected function casts(): array
    {
        return [
            'is_utama' => 'boolean',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function prodiKeahlian(): BelongsTo
    {
        return $this->belongsTo(ProdiKeahlian::class, 'prodi_keahlian_id');
    }
}
