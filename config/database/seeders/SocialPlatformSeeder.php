<?php

namespace Database\Seeders;

use App\Models\SocialPlatform;
use Illuminate\Database\Seeder;

class SocialPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            [
                'name' => 'Facebook',
                'slug' => 'facebook',
                'icon' => 'facebook',
            ],
            [
                'name' => 'Instagram',
                'slug' => 'instagram',
                'icon' => 'instagram',
            ],
            [
                'name' => 'TikTok',
                'slug' => 'tiktok',
                'icon' => 'tiktok',
            ],
            [
                'name' => 'YouTube',
                'slug' => 'youtube',
                'icon' => 'youtube',
            ],
            [
                'name' => 'LinkedIn',
                'slug' => 'linkedin',
                'icon' => 'linkedin',
            ],
        ];

        foreach ($platforms as $platform) {
            SocialPlatform::updateOrCreate(
                ['slug' => $platform['slug']],
                $platform
            );
        }
    }
}