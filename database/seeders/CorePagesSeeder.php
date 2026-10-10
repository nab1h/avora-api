<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class CorePagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'home' => [
                'en' => 'Manage your work simply',
                'ar' => 'أدر عملك ببساطة',
            ],

            'about' => [
                'en' => 'About AVORA',
                'ar' => 'من نحن',
            ],

            'contact' => [
                'en' => 'Contact Us',
                'ar' => 'تواصل معنا',
            ],

            'services' => [
                'en' => 'Our Services',
                'ar' => 'خدماتنا',
            ],
        ];

        $locales = config('website.locales', ['en']);

        foreach (config('website.pages', []) as $page) {
            foreach ($locales as $locale) {
                $title = $pages[$page][$locale] ?? null;

                if ($title === null) {
                    continue;
                }

                Content::firstOrCreate(
                    [
                        'page' => $page,
                        'section' => 'hero',
                        'key' => "title_{$locale}",
                    ],
                    [
                        'value' => $title,
                        'type' => 'text',
                        'gallery_id' => null,
                        'sort_order' => 1,
                    ]
                );
            }
        }
    }
}