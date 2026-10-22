<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpecificationResource;
use App\Models\Specification;
use App\Support\CatalogResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpecificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $specifications = Specification::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($request->integer('per_page', 15));

        return CatalogResponse::resource(
            SpecificationResource::collection($specifications),
            'catalog.specifications_retrieved'
        );
    }

    public function show(Specification $specification): JsonResponse
    {
        return CatalogResponse::resource(
            new SpecificationResource($specification),
            'catalog.specification_retrieved'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'spec_key' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('specifications', 'spec_key'),
            ],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:50'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $specification = Specification::create($data);

        return CatalogResponse::resource(
            new SpecificationResource($specification),
            'catalog.specification_created',
            201
        );
    }

    public function update(
        Request $request,
        Specification $specification
    ): JsonResponse {
        $data = $request->validate([
            'spec_key' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('specifications', 'spec_key')
                    ->ignore($specification->id),
            ],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:50'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $specification->update($data);

        return CatalogResponse::resource(
            new SpecificationResource($specification->refresh()),
            'catalog.specification_updated'
        );
    }

    public function destroy(
        Specification $specification
    ): JsonResponse {
        $specification->delete();

        return CatalogResponse::deleted(
            'catalog.specification_deleted'
        );
    }
}