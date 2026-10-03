<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContentResource;
use App\Models\Content;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    public function show(string $page): JsonResponse
    {
<<<<<<< HEAD
        $contents = Content::query()
            ->where('page', $page)
            ->with('galleryItem')
            ->orderBy('page')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => ContentResource::collection($contents),
        ]);
    }

    
}
=======
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
>>>>>>> 678db43 (done content)
