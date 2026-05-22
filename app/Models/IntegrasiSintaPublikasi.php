<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrasiSintaPublikasi extends Model
{
    use HasFactory;

    protected $table = 'integrasi_sinta_publikasi';

    protected $fillable = [
        'dosen_id', 'publikasi_id', 'judul', 'data_dari_sinta', 'status_sinkron', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'data_dari_sinta' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function publikasi(): BelongsTo
    {
        return $this->belongsTo(Publikasi::class);
    }
}
