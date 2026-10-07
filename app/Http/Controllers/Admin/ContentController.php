<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Content::query();

        if ($request->filled('page')) {
            $query->where('page', $request->page);
        }

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        $contents = $query
            ->with('galleryItem')
            ->orderBy('page')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => ContentResource::collection($contents),
        ]);
    }

    public function show(Content $content): JsonResponse
    {
        return response()->json([
            'data' => new ContentResource($content->load('galleryItem')),
        ]);
    }

    public function store(StoreContentRequest $request): JsonResponse
    {
        $content = Content::create(
            $request->validated()
        );

        $content->load('galleryItem');

        return response()->json([
            'message' => 'Content created successfully.',
            'data' => new ContentResource($content),
        ], 201);
    }

    public function update(
        UpdateContentRequest $request,
        Content $content
    ): JsonResponse {
        $data = $request->validated();
        $type = $data['type'] ?? $content->type;

        if ($type === 'image') {
            $data['value'] = null;
        } else {
            $data['gallery_id'] = null;
        }

        $content->update($data);

        return response()->json([
            'message' => 'Content updated successfully.',
            'data' => new ContentResource($content->fresh()->load('galleryItem')),
        ]);
    }

    public function destroy(Content $content): JsonResponse
    {
        $content->delete();

        return response()->json([
            'message' => 'Content deleted successfully.',
        ]);
    }

    public function pages(): JsonResponse
    {
        $pages = Content::query()
            ->select('page')
            ->selectRaw('MIN(id) as first_id')
            ->groupBy('page')
            ->orderBy('first_id')
            ->pluck('page');

        return response()->json([
            'data' => $pages,
        ]);
    }
}
