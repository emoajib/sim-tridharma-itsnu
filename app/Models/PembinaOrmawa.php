<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembinaOrmawa extends Model
{
    protected $table = 'm_pembina_ormawa';

    protected $fillable = [
        'ormawa_id',
        'dosen_id',
        'periode_id',
        'sk_pembina',
    ];

    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
