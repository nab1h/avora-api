<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryItemRequest;
use App\Http\Resources\GalleryItemResource;
use App\Models\GalleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class GalleryController extends Controller
{
    public function index()
    {
        $galleryItems = GalleryItem::latest()->get();

        return response()->json($galleryItems);
    }

    public function adminIndex()
    {
        $galleryItems = GalleryItem::latest()->get();

        return response()->json($galleryItems);
    }

    public function store(Request $request)
    {
        $request->validate([
            'images' => ['required', 'array'],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $galleryItems = [];

        foreach ($request->file('images') as $image) {
            $galleryItems[] = GalleryItem::create([
                'image' => $image->store('gallery', 'public'),
            ]);
        }

        return GalleryItemResource::collection($galleryItems);
    }

    public function destroy(GalleryItem $galleryItem): JsonResponse
    {
        if ($galleryItem->image) {
            Storage::disk('public')->delete($galleryItem->image);
        }

        $galleryItem->delete();

        return response()->json([
            'message' => 'Image deleted successfully.',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:gallery_items,id'],
        ]);

        $galleryItems = GalleryItem::whereIn('id', $request->ids)->get();

        foreach ($galleryItems as $galleryItem) {
            if ($galleryItem->image) {
                Storage::disk('public')->delete($galleryItem->image);
            }

            $galleryItem->delete();
        }

        return response()->json([
            'message' => 'Images deleted successfully.',
        ]);
    }
}
