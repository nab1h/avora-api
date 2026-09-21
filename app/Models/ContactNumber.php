<?php

namespace App\Models;

use App\Enums\ContactNumberType;
use Illuminate\Database\Eloquent\Model;

class ContactNumber extends Model
{
    protected $fillable = [
        'type',
        'number',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContactNumberType::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}