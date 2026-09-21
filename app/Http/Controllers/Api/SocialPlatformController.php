<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialPlatformResource;
use App\Models\SocialPlatform;

class SocialPlatformController extends Controller
{
    public function index()
    {
        $platforms = SocialPlatform::orderBy('name')->get();

        return SocialPlatformResource::collection($platforms);
    }
}