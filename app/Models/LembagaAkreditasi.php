<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LembagaAkreditasi extends Model
{
    use HasActiveScope, HasFactory;

    protected $table = 'm_lembaga_akreditasi';

    protected $fillable = ['nama_lembaga', 'singkatan', 'deskripsi', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function instrumen()
    {
        return $this->hasMany(InstrumenAkreditasi::class, 'lembaga_id');
    }

    public function prodi()
    {
        return $this->hasMany(Prodi::class, 'lembaga_akreditasi_id');
    }
}
