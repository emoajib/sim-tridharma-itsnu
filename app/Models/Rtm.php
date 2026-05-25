<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rtm extends Model
{
    use HasFactory;

    protected $table = 'trx_rtm';

    protected $fillable = [
        'judul', 'tanggal_rapat', 'agenda', 'notulen',
        'file_notulen', 'dipimpin_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rapat' => 'date',
        ];
    }

    public function dipimpinOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dipimpin_oleh');
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(RtmActionItem::class, 'rtm_id');
    }
}
