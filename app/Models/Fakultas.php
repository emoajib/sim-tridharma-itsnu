<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fakultas extends Model
{
    use SoftDeletes;

    protected $table = 'm_fakultas';

    protected $fillable = [
        'kode_fakultas', 'nama_fakultas', 'alamat', 'telepon', 'email', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function prodis()
    {
        return $this->hasMany(Prodi::class, 'fakultas_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($fakultas) {
            if ($fakultas->isForceDeleting()) {
                $fakultas->prodis()->forceDelete();
            } else {
                $fakultas->prodis()->delete();
            }
        });
    }
}
