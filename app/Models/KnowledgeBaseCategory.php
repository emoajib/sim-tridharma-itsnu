<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBaseCategory extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'singkatan', 'deskripsi'];

    public function documents(): HasMany
    {
        return $this->hasMany(KnowledgeBaseDocument::class, 'category_id');
    }
}
