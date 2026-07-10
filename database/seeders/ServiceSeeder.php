<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Braces & Orthodontics',
                'short_description' => 'Modern braces for a perfectly aligned smile.',
                'description' => 'Straighten your teeth with our modern braces and Invisalign options.',
                'icon' => 'sentiment_very_satisfied',
                'category' => 'Orthodontics',
                'price' => 25000,
                'price_max' => 80000,
                'duration_minutes' => 60,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Teeth Whitening',
                'short_description' => 'Professional whitening for a brighter smile.',
                'description' => 'Get a Hollywood smile with our professional whitening treatment.',
                'icon' => 'auto_awesome',
                'category' => 'Cosmetic',
                'price' => 5000,
                'price_max' => 8000,
                'duration_minutes' => 90,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Dental Implants',
                'short_description' => 'Permanent solution for missing teeth.',
                'description' => 'Replace missing teeth with titanium dental implants.',
                'icon' => 'medical_services',
                'category' => 'Restorative',
                'price' => 35000,
                'price_max' => 60000,
                'duration_minutes' => 120,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Root Canal Treatment',
                'short_description' => 'Save your natural teeth from infection.',
                'description' => 'Painless root canal therapy with modern techniques.',
                'icon' => 'healing',
                'category' => 'Restorative',
                'price' => 8000,
                'price_max' => 15000,
                'duration_minutes' => 60,
                'sort_order' => 4,
            ],
            [
                'title' => 'Dental Cleaning & Checkup',
                'short_description' => 'Regular cleaning for healthy teeth.',
                'description' => 'Professional cleaning to maintain healthy gums and prevent cavities.',
                'icon' => 'cleaning_services',
                'category' => 'Preventive',
                'price' => 1500,
                'price_max' => 3000,
                'duration_minutes' => 45,
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'title' => 'Pediatric Dentistry',
                'short_description' => 'Gentle dental care for children.',
                'description' => 'Specialized dental care for children in a friendly environment.',
                'icon' => 'child_care',
                'category' => 'Pediatric',
                'price' => 2000,
                'price_max' => 5000,
                'duration_minutes' => 30,
                'sort_order' => 6,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['title' => $service['title']], $service);
        }

        $this->command->info('✅ ' . count($services) . ' services seeded successfully!');
    }
}
