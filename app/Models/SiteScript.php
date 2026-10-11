<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteScript extends Model
{
    protected $fillable = [
        'name',
        'type',
        'url',
        'code',
        'placement',
        'strategy',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}