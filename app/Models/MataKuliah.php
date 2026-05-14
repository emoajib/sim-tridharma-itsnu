<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MataKuliah extends Model
{
    use SoftDeletes;

    protected $table = 'm_mata_kuliah';

    protected $fillable = [
        'kode_mk', 'nama_mk', 'sks', 'prodi_id', 'semester', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sks' => 'integer',
            'semester' => 'integer',
        ];
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}
