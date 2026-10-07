<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Dental Cleaning',
                'description' => 'Professional dental cleaning to remove plaque and maintain healthy teeth and gums.',
                'image' => 'services/dental-cleaning.jpg',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Teeth Whitening',
                'description' => 'Professional teeth whitening treatment for a brighter and more confident smile.',
                'image' => 'services/teeth-whitening.jpg',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Dental Implants',
                'description' => 'High-quality dental implants to replace missing teeth and restore your smile.',
                'image' => 'services/dental-implants.jpg',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Orthodontics',
                'description' => 'Modern orthodontic treatments to improve teeth alignment and create a healthier smile.',
                'image' => 'services/orthodontics.jpg',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Dental Crowns',
                'description' => 'Custom dental crowns designed to restore damaged or weakened teeth.',
                'image' => 'services/dental-crowns.jpg',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                [
                    'slug' => Str::slug($service['name']),
                ],
                $service
            );
        }
    }
}