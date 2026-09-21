<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialLinkRequest;
use App\Http\Requests\UpdateSocialLinkRequest;
use App\Http\Resources\SocialLinkResource;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;

class SocialLinkController extends Controller
{
    public function index()
    {
        $socialLinks = SocialLink::with('platform')
            ->orderBy('sort_order')
            ->get();

        return SocialLinkResource::collection($socialLinks);
    }

    public function adminIndex()
    {
        return SocialLinkResource::collection(
            SocialLink::with('platform')
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function store(StoreSocialLinkRequest $request)
    {
        $socialLink = SocialLink::create($request->validated());

        $socialLink->load('platform');

        return new SocialLinkResource($socialLink);
    }

    public function show(SocialLink $socialLink)
    {
        $socialLink->load('platform');

        return new SocialLinkResource($socialLink);
    }

    public function update(
        UpdateSocialLinkRequest $request,
        SocialLink $socialLink
    ) {
        $socialLink->update($request->validated());

        $socialLink->load('platform');

        return new SocialLinkResource($socialLink);
    }

    public function destroy(SocialLink $socialLink): JsonResponse
    {
        $socialLink->delete();

        return response()->json([
            'message' => 'Social link deleted successfully.',
        ]);
    }
}
