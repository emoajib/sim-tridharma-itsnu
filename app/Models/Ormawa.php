<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ormawa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_ormawa';

    protected $fillable = [
        'nama',
        'kategori',
        'prodi_id',
        'visi_misi',
        'file_ad_art',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function pembinas()
    {
        return $this->hasMany(PembinaOrmawa::class);
    }

    public function proposals()
    {
        return $this->hasMany(ProposalKegiatan::class);
    }

    public function asets()
    {
        return $this->hasMany(AsetOrmawa::class);
    }
}
