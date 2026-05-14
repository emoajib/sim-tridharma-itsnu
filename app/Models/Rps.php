<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rps extends Model
{
    use SoftDeletes;

    protected $table = 'm_rps';

    protected $fillable = [
        'mata_kuliah_id', 'prodi_id', 'periode_id', 'kode_rps', 'file_path', 'status'
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }
}
