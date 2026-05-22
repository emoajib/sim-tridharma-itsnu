<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keuangan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_keuangan';

    protected $fillable = ['dosen_id', 'prodi_id', 'periode_id', 'jenis_dana', 'sumber_dana', 'jumlah', 'tahun', 'keterangan', 'status'];

    protected $guarded = ['is_verified'];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'jumlah' => 'decimal:2',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }
}
