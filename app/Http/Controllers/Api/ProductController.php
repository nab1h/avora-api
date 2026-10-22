<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogIndexRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\CatalogQuery;
use App\Services\CatalogService;
use App\Support\CatalogResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(CatalogIndexRequest $request, CatalogQuery $query): JsonResponse
    {
        return CatalogResponse::resource(new ProductCollection($query->products($request->validated())), 'catalog.products_retrieved');
    }

    public function adminIndex(CatalogIndexRequest $request, CatalogQuery $query): JsonResponse
    {
        return CatalogResponse::resource(new ProductCollection($query->products($request->validated(), true)), 'catalog.products_retrieved');
    }

    public function categoryProducts(CatalogIndexRequest $request, Category $category, CatalogQuery $query): JsonResponse
    {
        abort_unless($category->status === 'active', 404);

        return CatalogResponse::resource(new ProductCollection($query->products($request->validated(), false, $category)), 'catalog.products_retrieved');
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless(Product::visible()->whereKey($product->id)->exists(), 404);

        return $this->adminShow($product);
    }

    public function adminShow(Product $product): JsonResponse
    {
        return CatalogResponse::resource(
            new ProductResource(
                $product->loadMissing([
                    'category',
                    'images',
                    'specifications.specification',
                ])
            ),
            'catalog.product_retrieved'
        );
    }

    public function store(StoreProductRequest $request, CatalogService $service): JsonResponse
    {
        return CatalogResponse::resource(new ProductResource($service->saveProduct($request->validated())), 'catalog.product_created', 201);
    }

    public function update(UpdateProductRequest $request, Product $product, CatalogService $service): JsonResponse
    {
        return CatalogResponse::resource(new ProductResource($service->saveProduct($request->validated(), $product)), 'catalog.product_updated');
    }

    public function destroy(Product $product, CatalogService $service): JsonResponse
    {
        $service->deleteProduct($product);

        return CatalogResponse::deleted('catalog.product_deleted');
    }
}
