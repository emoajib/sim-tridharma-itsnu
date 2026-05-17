<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LembagaAkreditasi extends Model
{
    protected $table = 'm_lembaga_akreditasi';

    protected $fillable = ['nama_lembaga', 'singkatan', 'deskripsi', 'is_active'];

    public function instrumen()
    {
        return $this->hasMany(InstrumenAkreditasi::class, 'lembaga_id');
    }

    public function prodi()
    {
        return $this->hasMany(Prodi::class, 'lembaga_akreditasi_id');
    }
}
