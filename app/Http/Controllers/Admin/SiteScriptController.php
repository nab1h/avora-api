<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiteScriptRequest;
use App\Http\Requests\UpdateSiteScriptRequest;
use App\Http\Resources\SiteScriptResource;
use App\Models\SiteScript;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SiteScriptController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SiteScriptResource::collection(
            SiteScript::query()
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
        );
    }

    public function store(
        StoreSiteScriptRequest $request
    ): SiteScriptResource {
        $siteScript = SiteScript::create(
            $request->validated()
        );

        return new SiteScriptResource($siteScript);
    }

    public function show(
        SiteScript $siteScript
    ): SiteScriptResource {
        return new SiteScriptResource($siteScript);
    }

    public function update(
        UpdateSiteScriptRequest $request,
        SiteScript $siteScript
    ): SiteScriptResource {
        $siteScript->update(
            $request->validated()
        );

        return new SiteScriptResource(
            $siteScript->refresh()
        );
    }

    public function destroy(
        SiteScript $siteScript
    ): \Illuminate\Http\JsonResponse {
        $siteScript->delete();

        return response()->json([
            'message' => 'Site script deleted successfully.',
        ]);
    }
}