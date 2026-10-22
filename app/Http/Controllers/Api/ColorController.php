<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreColorRequest;
use App\Http\Requests\UpdateColorRequest;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use App\Support\CatalogResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ColorController extends Controller
{
    public function index(): JsonResponse
    {
        $colors = Color::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return CatalogResponse::resource(
            ColorResource::collection($colors),
            'catalog.colors_retrieved'
        );
    }

    public function show(Color $color): JsonResponse
    {
        return CatalogResponse::resource(
            new ColorResource($color),
            'catalog.color_retrieved'
        );
    }

    public function store(
        StoreColorRequest $request
    ): JsonResponse {
        $color = Color::create($request->validated());

        return CatalogResponse::resource(
            new ColorResource($color),
            'catalog.color_created',
            201
        );
    }

    public function update(
        UpdateColorRequest $request,
        Color $color
    ): JsonResponse {
        $color->update($request->validated());

        return CatalogResponse::resource(
            new ColorResource($color->refresh()),
            'catalog.color_updated'
        );
    }

    public function destroy(Color $color): JsonResponse
    {
        if ($color->products()->exists()) {
            throw ValidationException::withMessages([
                'color' => [
                    __('catalog.color_has_products'),
                ],
            ]);
        }

        $color->delete();

        return CatalogResponse::deleted(
            'catalog.color_deleted'
        );
    }
}
