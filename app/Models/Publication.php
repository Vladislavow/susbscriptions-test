<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Publication extends Model
{
    use HasFactory;

    const STATUSES = [
        self::DRAFT_STATUS,
        self::ACTIVE_STATUS,
        self::ARCHIVED_STATUS,
    ];

    const DRAFT_STATUS = 'draft';
    const ACTIVE_STATUS = 'active';
    const ARCHIVED_STATUS = 'archived';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'status'
    ];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
