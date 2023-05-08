<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'max_publications',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function isInactive(): bool
    {
        return !$this->active;
    }
}
