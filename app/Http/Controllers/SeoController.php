<?php

namespace App\Http\Controllers;

use App\Http\Resources\SeoPageResource;
use App\Models\SeoPage;
use Illuminate\Http\JsonResponse;

class SeoController extends Controller
{
    public function show(string $page): JsonResponse
    {
        $seoPage = SeoPage::where('page', $page)->firstOrFail();

        return response()->json([
            'data' => new SeoPageResource($seoPage),
        ]);
    }
}