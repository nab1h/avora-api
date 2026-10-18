<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogIndexRequest;
use App\Http\Requests\StoreProductImagesRequest;
use App\Http\Requests\UpdateProductImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Product;
use App\Services\CatalogService;
use App\Support\CatalogResponse;
use Illuminate\Http\JsonResponse;

class ProductImageController extends Controller
{
    public function index(CatalogIndexRequest $request, Product $product): JsonResponse
    {
        abort_unless(Product::visible()->whereKey($product->id)->exists(), 404);

        return CatalogResponse::resource(ImageResource::collection($product->images()->paginate($request->validated('per_page', 15))), 'catalog.images_retrieved');
    }

    public function store(StoreProductImagesRequest $request, Product $product, CatalogService $service): JsonResponse
    {
        $images = $service->uploadImages($product, $request->file('images'));

        return CatalogResponse::resource(ImageResource::collection($images), 'catalog.images_created', 201);
    }

    public function update(UpdateProductImageRequest $request, Product $product, Image $image): JsonResponse
    {
        // Also enforce ownership explicitly, independent of scoped bindings.
        abort_unless($image->product_id === $product->id, 404);
        $image->update($request->validated());

        return CatalogResponse::resource(new ImageResource($image->refresh()), 'catalog.image_updated');
    }

    public function destroy(Product $product, Image $image, CatalogService $service): JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        $service->deleteImage($product, $image);

        return CatalogResponse::deleted('catalog.image_deleted');
    }
}
