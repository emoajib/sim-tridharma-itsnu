<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use HasActiveScope, SoftDeletes;

    protected $table = 'm_mahasiswa';

    protected $fillable = [
        'nim', 'nama', 'prodi_id', 'angkatan', 'status', 'email', 'telepon', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}
