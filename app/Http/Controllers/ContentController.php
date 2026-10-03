<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContentResource;
use App\Models\Content;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    public function show(string $page): JsonResponse
    {
        $contents = Content::where('page', $page)
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        $data = $contents->map(function ($section) {
            return ContentResource::collection($section);
        });

        return response()->json([
            'data' => $data,
        ]);
    }
}