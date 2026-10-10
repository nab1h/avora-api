<?php

namespace App\Http\Controllers;

use App\Http\Resources\SeoPageResource;
use App\Models\SeoPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function show(
        Request $request,
        string $page
    ): JsonResponse {
        $locale = $request->query('locale', 'en');

        $seoPage = SeoPage::query()
            ->where('page', $page)
            ->where('locale', $locale)
            ->firstOrFail();

        return response()->json([
            'data' => new SeoPageResource($seoPage),
        ]);
    }
}