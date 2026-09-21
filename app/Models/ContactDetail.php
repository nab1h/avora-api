<?php

namespace App\Models;

use App\Enums\ContactDetailType;
use Illuminate\Database\Eloquent\Model;

class ContactDetail extends Model
{
    protected $fillable = [
        'type',
        'value',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContactDetailType::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}