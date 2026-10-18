<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogIndexRequest;
use App\Http\Requests\SaveGalleryRequest;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use App\Services\CatalogService;
use App\Support\CatalogResponse;
use Illuminate\Http\JsonResponse;

class CatalogGalleryController extends Controller
{
    public function index(CatalogIndexRequest $request): JsonResponse
    {
        return CatalogResponse::resource(GalleryResource::collection(Gallery::with('items')->orderBy('id')->paginate($request->validated('per_page', 15))), 'catalog.galleries_retrieved');
    }

    public function show(Gallery $gallery): JsonResponse
    {
        return CatalogResponse::resource(new GalleryResource($gallery->loadMissing('items')), 'catalog.gallery_retrieved');
    }

    public function store(SaveGalleryRequest $request, CatalogService $service): JsonResponse
    {
        return CatalogResponse::resource(new GalleryResource($service->saveGallery($request->validated())), 'catalog.gallery_created', 201);
    }

    public function update(SaveGalleryRequest $request, Gallery $gallery, CatalogService $service): JsonResponse
    {
        return CatalogResponse::resource(new GalleryResource($service->saveGallery($request->validated(), $gallery)), 'catalog.gallery_updated');
    }

    public function destroy(Gallery $gallery): JsonResponse
    {
        $gallery->delete();

        return CatalogResponse::deleted('catalog.gallery_deleted');
    }
}
