<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPlatform extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
    ];

    public function links(): HasMany
    {
        return $this->hasMany(SocialLink::class);
    }

    
}
