<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'AVORA',
                'type' => 'string',
            ],
            [
                'group' => 'general',
                'key' => 'site_description',
                'value' => null,
                'type' => 'string',
            ],

            [
                'group' => 'branding',
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'branding',
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'branding',
                'key' => 'site_apple_icon',
                'value' => null,
                'type' => 'image',
            ],

            [
                'group' => 'branding',
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'branding',
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'branding',
                'key' => 'site_apple_icon',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'branding',
                'key' => 'site_android_icon',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'branding',
                'key' => 'site_maskable_icon',
                'value' => null,
                'type' => 'image',
            ],

            [
                'group' => 'contact',
                'key' => 'contact_email',
                'value' => null,
                'type' => 'string',
            ],
            [
                'group' => 'contact',
                'key' => 'contact_phone',
                'value' => null,
                'type' => 'string',
            ],
            [
                'group' => 'contact',
                'key' => 'contact_address',
                'value' => null,
                'type' => 'text',
            ],

            [
                'group' => 'maps',
                'key' => 'google_maps_url',
                'value' => null,
                'type' => 'url',
            ],
            [
                'group' => 'maps',
                'key' => 'google_maps_embed_url',
                'value' => null,
                'type' => 'url',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
