<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetOrmawa extends Model
{
    protected $table = 'trx_aset_ormawa';

    protected $fillable = [
        'ormawa_id',
        'nama_aset',
        'jumlah',
        'luas_ruang_m2',
        'kondisi',
        'tahun_perolehan',
    ];

    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class);
    }
}
