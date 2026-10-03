<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSeoPageRequest;
use App\Http\Resources\SeoPageResource;
use App\Models\SeoPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeoPageController extends Controller
{
    public function index(): JsonResponse
    {
        $pages = SeoPage::latest()->get();

        return response()->json([
            'data' => SeoPageResource::collection($pages),
        ]);
    }

    public function store(UpdateSeoPageRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('og_image')) {
            $data['og_image'] = $request->file('og_image')
                ->store('seo', 'public');
        }

        $seoPage = SeoPage::create($data);

        return response()->json([
            'message' => 'SEO page created successfully.',
            'data' => new SeoPageResource($seoPage),
        ], 201);
    }

    public function show(SeoPage $seoPage): JsonResponse
    {
        return response()->json([
            'data' => new SeoPageResource($seoPage),
        ]);
    }

    public function update(
        UpdateSeoPageRequest $request,
        SeoPage $seoPage
    ): JsonResponse {
        $data = $request->validated();

        if ($request->hasFile('og_image')) {
            if ($seoPage->og_image) {
                Storage::disk('public')->delete($seoPage->og_image);
            }

            $data['og_image'] = $request->file('og_image')
                ->store('seo', 'public');
        }

        $seoPage->update($data);

        return response()->json([
            'message' => 'SEO page updated successfully.',
            'data' => new SeoPageResource($seoPage->fresh()),
        ]);
    }

    public function destroy(SeoPage $seoPage): JsonResponse
    {
        if ($seoPage->og_image) {
            Storage::disk('public')->delete($seoPage->og_image);
        }

        $seoPage->delete();

        return response()->json([
            'message' => 'SEO page deleted successfully.',
        ]);
    }
}