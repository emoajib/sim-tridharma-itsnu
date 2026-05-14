<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Keuangan extends Model {
    use SoftDeletes;
    protected $table = 'trx_keuangan';
    protected $fillable = ['dosen_id','prodi_id','periode_id','jenis_dana','sumber_dana','jumlah','tahun','keterangan','status','is_verified'];
    protected function casts(): array { return ['is_verified'=>'boolean','jumlah'=>'decimal:2']; }
    public function dosen() { return $this->belongsTo(Dosen::class); }
    public function prodi() { return $this->belongsTo(Prodi::class); }
    public function periode() { return $this->belongsTo(PeriodeAkademik::class,'periode_id'); }
}
