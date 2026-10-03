<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    protected $fillable = [
        'page',
        'title',
        'description',
        'keywords',
        'og_title',
        'og_description',
        'og_image',
        'robots',
        'canonical_url',
    ];
}