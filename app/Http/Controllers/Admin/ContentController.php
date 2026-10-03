<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            ->orderBy('page')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => ContentResource::collection($contents),
        ]);
    }

    public function store(StoreContentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['value'] = $request->file('image')
                ->store('contents', 'public');
        }

        unset($data['image']);

        $content = Content::create($data);

        return response()->json([
            'message' => 'Content created successfully.',
            'data' => new ContentResource($content),
        ], 201);
    }

    public function show(Content $content): JsonResponse
    {
        return response()->json([
            'data' => new ContentResource($content),
        ]);
    }

    public function update(
        UpdateContentRequest $request,
        Content $content
    ): JsonResponse {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($content->type === 'image' && $content->value) {
                Storage::disk('public')->delete($content->value);
            }

            $data['value'] = $request->file('image')
                ->store('contents', 'public');
        }

        unset($data['image']);

        $content->update($data);

        return response()->json([
            'message' => 'Content updated successfully.',
            'data' => new ContentResource($content->fresh()),
        ]);
    }

    public function destroy(Content $content): JsonResponse
    {
        if ($content->type === 'image' && $content->value) {
            Storage::disk('public')->delete($content->value);
        }

        $content->delete();

        return response()->json([
            'message' => 'Content deleted successfully.',
        ]);
    }

    public function pages(): JsonResponse
    {
        $pages = Content::query()
            ->select('page')
            ->distinct()
            ->orderBy('page')
            ->pluck('page');

        return response()->json([
            'data' => $pages,
        ]);
    }
}
