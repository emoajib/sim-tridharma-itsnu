<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegrasiSintaPenelitian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'integrasi_sinta_penelitian';

    protected $fillable = [
        'dosen_id', 'penelitian_id', 'judul', 'tahun', 'skema',
        'jumlah_dana', 'data_dari_sinta', 'status_sinkron', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'data_dari_sinta' => 'array',
            'jumlah_dana' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function penelitian(): BelongsTo
    {
        return $this->belongsTo(Penelitian::class);
    }
}
