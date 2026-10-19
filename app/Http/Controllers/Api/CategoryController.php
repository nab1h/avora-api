<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryIndexRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CatalogQuery;
use App\Services\CatalogService;
use App\Support\CatalogResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(CategoryIndexRequest $request, CatalogQuery $query): JsonResponse
    {
        return CatalogResponse::resource(new CategoryCollection($query->categories($request->validated())), 'catalog.categories_retrieved');
    }

    public function adminIndex(CategoryIndexRequest $request, CatalogQuery $query): JsonResponse
    {
        return CatalogResponse::resource(new CategoryCollection($query->categories($request->validated(), true)), 'catalog.categories_retrieved');
    }

    public function show(Category $category): JsonResponse
    {
        abort_unless($category->status === 'active', 404);

        return $this->adminShow($category);
    }

    public function adminShow(Category $category): JsonResponse
    {
        return CatalogResponse::resource(
            new CategoryResource($category->loadMissing('galleryItem')),
            'catalog.category_retrieved'
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated())
            ->refresh()
            ->load('galleryItem');

        return CatalogResponse::resource(
            new CategoryResource($category),
            'catalog.category_created',
            201
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return CatalogResponse::resource(
            new CategoryResource($category->refresh()->load('galleryItem')),
            'catalog.category_updated'
        );
    }

    public function destroy(Category $category, CatalogService $service): JsonResponse
    {
        $service->deleteCategory($category);

        return CatalogResponse::deleted('catalog.category_deleted');
    }
}
