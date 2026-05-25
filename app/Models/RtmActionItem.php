<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtmActionItem extends Model
{
    use HasFactory;

    protected $table = 'trx_rtm_action_items';

    protected $fillable = [
        'rtm_id', 'deskripsi', 'pic_user_id', 'deadline',
        'status', 'hasil', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function rtm(): BelongsTo
    {
        return $this->belongsTo(Rtm::class);
    }

    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }
}
