<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use App\Models\Traits\HasCascadeDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fakultas extends Model
{
    use HasActiveScope, HasCascadeDeletes, HasFactory, SoftDeletes;

    protected array $cascadeDeletes = ['prodis'];

    protected $table = 'm_fakultas';

    protected $fillable = [
        'kode_fakultas', 'nama_fakultas', 'alamat', 'telepon', 'email', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function prodis(): HasMany
    {
        return $this->hasMany(Prodi::class, 'fakultas_id');
    }
}
