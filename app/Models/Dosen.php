<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen extends Model
{
    use SoftDeletes;

    protected $table = 'm_dosen';

    protected $fillable = [
        'nidn', 'nip', 'nama_depan', 'nama_belakang', 'gelar_depan', 'gelar_belakang',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'prodi_id',
        'pendidikan_terakhir', 'jabatan_fungsional', 'status_aktivitas',
        'email', 'telepon', 'is_active', 'sinta_id', 'sinta_score_overall',
        'sinta_score_3yr', 'status_verifikasi_sinta'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_lahir' => 'date',
        ];
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function pendidikan()
    {
        return $this->hasMany(KegiatanPendidikan::class, 'dosen_id');
    }

    public function penelitian()
    {
        return $this->hasMany(Penelitian::class, 'dosen_id');
    }

    public function publikasi()
    {
        return $this->hasMany(Publikasi::class, 'dosen_id');
    }

    public function pkm()
    {
        return $this->hasMany(Pkm::class, 'dosen_id');
    }

    public function bkd()
    {
        return $this->hasMany(Bkd::class, 'dosen_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($dosen) {
            $relations = ['pendidikan', 'penelitian', 'publikasi', 'pkm', 'bkd'];
            
            foreach ($relations as $relation) {
                if ($dosen->isForceDeleting()) {
                    $dosen->{$relation}()->forceDelete();
                } else {
                    $dosen->{$relation}()->delete();
                }
            }
        });
    }
}
