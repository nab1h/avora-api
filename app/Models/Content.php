<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = [
        'page',
        'section',
        'key',
        'value',
        'type',
        'sort_order',
    ];
}