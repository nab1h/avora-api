<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
<<<<<<< HEAD

=======
use Illuminate\Support\Facades\Storage;
>>>>>>> 678db43 (done content)

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
<<<<<<< HEAD
            ->with('galleryItem')
=======
>>>>>>> 678db43 (done content)
            ->orderBy('page')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => ContentResource::collection($contents),
        ]);
    }

<<<<<<< HEAD
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
=======
    public function store(StoreContentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['value'] = $request->file('image')
                ->store('contents', 'public');
        }

        unset($data['image']);

        $content = Content::create($data);
>>>>>>> 678db43 (done content)

        return response()->json([
            'message' => 'Content created successfully.',
            'data' => new ContentResource($content),
        ], 201);
    }

<<<<<<< HEAD
=======
    public function show(Content $content): JsonResponse
    {
        return response()->json([
            'data' => new ContentResource($content),
        ]);
    }
>>>>>>> 678db43 (done content)

    public function update(
        UpdateContentRequest $request,
        Content $content
    ): JsonResponse {
        $data = $request->validated();
<<<<<<< HEAD
        $type = $data['type'] ?? $content->type;

        if ($type === 'image') {
            $data['value'] = null;
        } else {
            $data['gallery_id'] = null;
        }

=======

        if ($request->hasFile('image')) {
            if ($content->type === 'image' && $content->value) {
                Storage::disk('public')->delete($content->value);
            }

            $data['value'] = $request->file('image')
                ->store('contents', 'public');
        }

        unset($data['image']);

>>>>>>> 678db43 (done content)
        $content->update($data);

        return response()->json([
            'message' => 'Content updated successfully.',
<<<<<<< HEAD
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
=======
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
>>>>>>> 678db43 (done content)
}
