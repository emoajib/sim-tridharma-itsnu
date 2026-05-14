<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Kerjasama extends Model {
    use SoftDeletes;
    protected $table = 'm_kerjasama';
    protected $fillable = ['mitra_id','prodi_id','jenis_kerjasama','nomor_mou','tanggal_mulai','tanggal_selesai','status','file_dokumen','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean','tanggal_mulai'=>'date','tanggal_selesai'=>'date']; }
    public function mitra() { return $this->belongsTo(Mitra::class); }
    public function prodi() { return $this->belongsTo(Prodi::class); }
}
