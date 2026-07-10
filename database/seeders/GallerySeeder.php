<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            [
                'title' => 'Modern Reception Area',
                'description' => 'Welcoming and comfortable waiting area',
                'image' => 'gallery/placeholder-1.jpg',
                'category' => 'clinic',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Treatment Room',
                'description' => 'State-of-the-art dental treatment room',
                'image' => 'gallery/placeholder-2.jpg',
                'category' => 'clinic',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Latest Dental Equipment',
                'description' => 'Advanced technology for better care',
                'image' => 'gallery/placeholder-3.jpg',
                'category' => 'equipment',
                'sort_order' => 3,
            ],
            [
                'title' => 'Our Expert Team',
                'description' => 'Meet our experienced dental professionals',
                'image' => 'gallery/placeholder-4.jpg',
                'category' => 'team',
                'is_featured' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($galleries as $gallery) {
            Gallery::updateOrCreate(
                ['title' => $gallery['title']],
                array_merge($gallery, ['is_active' => true])
            );
        }

        $this->command->info('✅ ' . count($galleries) . ' gallery items seeded!');
    }
}
