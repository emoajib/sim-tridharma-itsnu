<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MataKuliah extends Model
{
    use HasActiveScope, HasFactory, SoftDeletes;

    protected $table = 'm_mata_kuliah';

    protected $fillable = [
        'kode_mk', 'nama_mk', 'sks', 'prodi_id', 'semester', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sks' => 'integer',
            'semester' => 'integer',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function kurikulums(): BelongsToMany
    {
        return $this->belongsToMany(Kurikulum::class, 'm_mk_kurikulum', 'mata_kuliah_id', 'kurikulum_id')
            ->withPivot('semester_rekomendasi')
            ->withTimestamps();
    }
}
