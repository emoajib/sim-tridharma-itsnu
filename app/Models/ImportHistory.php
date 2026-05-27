<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'file_name',
        'file_path',
        'total_rows',
        'success_rows',
        'failed_rows',
        'errors',
        'user_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'total_rows' => 'integer',
            'success_rows' => 'integer',
            'failed_rows' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
