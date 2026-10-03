<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()
            ->groupBy('group')
            ->map(function ($group) {
                return $group->pluck('value', 'key');
            });

        return response()->json([
            'data' => $settings,
        ]);
    }
}