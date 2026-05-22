<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kurikulum extends Model
{
    use HasActiveScope, SoftDeletes;

    protected $table = 'm_kurikulum';

    protected $fillable = [
        'nama_kurikulum', 'prodi_id', 'tahun_berlaku', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function mataKuliahs()
    {
        return $this->belongsToMany(MataKuliah::class, 'm_mk_kurikulum', 'kurikulum_id', 'mata_kuliah_id')
            ->withPivot('semester_rekomendasi')
            ->withTimestamps();
    }
}
