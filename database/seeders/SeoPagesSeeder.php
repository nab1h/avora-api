<?php

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

class SeoPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = config('website.pages', [
            'home',
            'about',
            'contact',
            'services',
        ]);

        $locales = config('website.locales', ['en']);

        $seoData = [
            'home' => [
                'en' => [
                    'title' => 'AVORA - Digital Cards',
                    'description' => 'Create your professional digital identity with AVORA.',
                    'keywords' => 'AVORA, digital cards, NFC, smart cards',
                ],
                'ar' => [
                    'title' => 'AVORA - بطاقات رقمية ذكية',
                    'description' => 'أنشئ هويتك الرقمية الاحترافية وشارك بياناتك بسهولة مع AVORA.',
                    'keywords' => 'أفورا, بطاقات رقمية, NFC, بطاقات ذكية',
                ],
            ],

            'about' => [
                'en' => [
                    'title' => 'About AVORA',
                    'description' => 'Learn more about AVORA and our digital solutions.',
                    'keywords' => 'About AVORA, digital identity, digital cards',
                ],
                'ar' => [
                    'title' => 'من نحن | AVORA',
                    'description' => 'تعرّف على AVORA وحلولنا الرقمية.',
                    'keywords' => 'من نحن, أفورا, الهوية الرقمية, البطاقات الرقمية',
                ],
            ],

            'services' => [
                'en' => [
                    'title' => 'Our Services | AVORA',
                    'description' => 'Explore the digital services offered by AVORA.',
                    'keywords' => 'AVORA services, digital services, NFC',
                ],
                'ar' => [
                    'title' => 'خدماتنا | AVORA',
                    'description' => 'اكتشف الخدمات والحلول الرقمية التي تقدمها AVORA.',
                    'keywords' => 'خدمات أفورا, خدمات رقمية, NFC',
                ],
            ],

            'contact' => [
                'en' => [
                    'title' => 'Contact Us | AVORA',
                    'description' => 'Contact AVORA for more information about our services.',
                    'keywords' => 'Contact AVORA, customer support, contact us',
                ],
                'ar' => [
                    'title' => 'تواصل معنا | AVORA',
                    'description' => 'تواصل مع AVORA لمعرفة المزيد عن خدماتنا.',
                    'keywords' => 'تواصل مع أفورا, خدمة العملاء, اتصل بنا',
                ],
            ],
        ];

        foreach ($pages as $page) {
            foreach ($locales as $locale) {
                $data = $seoData[$page][$locale] ?? null;

                if (! $data) {
                    continue;
                }

                SeoPage::firstOrCreate(
                    [
                        'page' => $page,
                        'locale' => $locale,
                    ],
                    [
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'keywords' => $data['keywords'],
                        'og_title' => $data['title'],
                        'og_description' => $data['description'],
                        'og_image' => null,
                        'robots' => 'index,follow',
                        'canonical_url' => null,
                    ]
                );
            }
        }
    }
}