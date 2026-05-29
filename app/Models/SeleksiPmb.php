<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeleksiPmb extends Model
{
    use HasFactory;

    protected $table = 'trx_seleksi_pmb';

    protected $fillable = [
        'periode_id',
        'prodi_id',
        'pendaftar',
        'lulus_seleksi',
        'daftar_ulang',
        'maba_reguler',
        'maba_transfer',
        'maba_asing_ft',
        'maba_asing_pt',
    ];

    protected function casts(): array
    {
        return [
            'pendaftar' => 'integer',
            'lulus_seleksi' => 'integer',
            'daftar_ulang' => 'integer',
            'maba_reguler' => 'integer',
            'maba_transfer' => 'integer',
            'maba_asing_ft' => 'integer',
            'maba_asing_pt' => 'integer',
        ];
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}
