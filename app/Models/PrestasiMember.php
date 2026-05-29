<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestasiMember extends Model
{
    use HasFactory;

    protected $table = 'trx_prestasi_member';

    protected $fillable = [
        'prestasi_id',
        'mahasiswa_id',
        'peran',
        'nominal_reward',
        'status_reward',
    ];

    public function prestasi()
    {
        return $this->belongsTo(Prestasi::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
