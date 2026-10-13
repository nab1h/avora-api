<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SiteScript;
use Illuminate\Http\JsonResponse;

class PublicSiteScriptController extends Controller
{
    public function index(): JsonResponse
    {
        $scripts = SiteScript::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'type',
                'url',
                'code',
                'placement',
                'strategy',
                'sort_order',
            ]);

        return response()->json([
            'data' => $scripts,
        ]);
    }
}