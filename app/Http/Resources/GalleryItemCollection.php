<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GalleryItemCollection extends ResourceCollection
{
    public $collects = GalleryItemResource::class;
}
