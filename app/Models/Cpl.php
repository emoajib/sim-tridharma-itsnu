<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cpl extends Model
{
    use SoftDeletes;

    protected $table = 'm_cpl';

    protected $fillable = [
        'kode_cpl', 'prodi_id', 'deskripsi', 'jenis', 'is_active'
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
