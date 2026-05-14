<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Mitra extends Model {
    use SoftDeletes;
    protected $table = 'm_mitra';
    protected $fillable = ['nama_mitra','jenis_mitra','alamat','kontak','telepon','email','is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
