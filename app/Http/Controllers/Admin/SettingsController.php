<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()
            ->groupBy('group')
            ->map(function ($group) {
                return SettingResource::collection($group);
            });

        return response()->json([
            'data' => $settings,
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $textSettings = [
            'site_name' => [
                'group' => 'general',
                'type' => 'string',
            ],

            'site_description' => [
                'group' => 'general',
                'type' => 'string',
            ],

            'contact_email' => [
                'group' => 'contact',
                'type' => 'string',
            ],

            'contact_phone' => [
                'group' => 'contact',
                'type' => 'string',
            ],

            'contact_whatsapp' => [
                'group' => 'contact',
                'type' => 'string',
            ],

            'contact_address' => [
                'group' => 'contact',
                'type' => 'text',
            ],
            'contact_address_ar' => [
                'group' => 'contact',
                'type' => 'text',
            ],

            'google_maps_url' => [
                'group' => 'maps',
                'type' => 'url',
            ],

            'google_maps_embed_url' => [
                'group' => 'maps',
                'type' => 'url',
            ],
        ];

        foreach ($textSettings as $key => $config) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $config['group'],
                    'value' => $data[$key] ?? null,
                    'type' => $config['type'],
                ]
            );
        }

        $imageSettings = [
            'site_logo' => 'logo',
            'site_favicon' => 'favicon',
            'site_apple_icon' => 'apple-icon',
            'site_android_icon' => 'android-icon',
            'site_maskable_icon' => 'maskable-icon',
        ];

        foreach ($imageSettings as $key => $folder) {
            if ($request->hasFile($key)) {
                $setting = Setting::where('key', $key)->first();

                if ($setting?->value) {
                    Storage::disk('public')->delete($setting->value);
                }

                $path = $request->file($key)->store(
                    "settings/{$folder}",
                    'public'
                );

                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'group' => 'branding',
                        'value' => $path,
                        'type' => 'image',
                    ]
                );
            }
        }

        $settings = Setting::all()
            ->groupBy('group')
            ->map(function ($group) {
                return SettingResource::collection($group);
            });

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => $settings,
        ]);
    }
}