<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FasilitasInternet extends Model
{
    protected $table = 'trx_fasilitas_internet';

    protected $fillable = [
        'periode_id',
        'bandwidth_total_mbps',
        'jumlah_mahasiswa_aktif',
        'rasio_mbps_per_mhs',
        'jumlah_titik_hotspot',
    ];

    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
