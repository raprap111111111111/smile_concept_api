<?php

namespace Database\Seeders;

use App\Models\Treatment;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $treatments = [
            [
                'name' => 'General Consultation & Oral Exam',
                'description' => 'Comprehensive check-up, digital x-ray review, and formulation of a personalized treatment plan.',
                'price' => 500.00,
                'estimated_duration_minutes' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Oral Prophylaxis (Teeth Cleaning)',
                'description' => 'Removal of plaque, tartar, and surface stains. Highly recommended every 6 months.',
                'price' => 1200.00,
                'estimated_duration_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Composite Filling (One Surface)',
                'description' => 'Tooth-colored resin restoration to repair cavities, chipped teeth, or wear.',
                'price' => 1500.00,
                'estimated_duration_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Simple Tooth Extraction',
                'description' => 'Safe removal of a visible, non-restorable tooth under local anesthesia.',
                'price' => 1800.00,
                'estimated_duration_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Laser Teeth Whitening',
                'description' => 'In-chair bleaching procedure utilizing advanced light system for rapid, premium whitening.',
                'price' => 8500.00,
                'estimated_duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Root Canal Therapy (Molar)',
                'description' => 'Specialized endodontic therapy to remove infection inside a molar, sealing the pulp chamber to salvage the tooth structure.',
                'price' => 12000.00,
                'estimated_duration_minutes' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Porcelain Zirconia Crown',
                'description' => 'High-strength, premium prosthetic cap designed to encase and protect a heavily damaged tooth.',
                'price' => 18000.00,
                'estimated_duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Surgical Wisdom Tooth Extraction',
                'description' => 'Minor oral surgical procedure to extract impacted wisdom teeth from bone structure.',
                'price' => 10000.00,
                'estimated_duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Deep Scaling & Root Planing (Per Quadrant)',
                'description' => 'Specialized deep cleaning procedure to treat early to moderate periodontal (gum) disease.',
                'price' => 2500.00,
                'estimated_duration_minutes' => 45,
                'is_active' => true,
            ]
        ];

        foreach ($treatments as $treatment) {
            Treatment::updateOrCreate(
                ['name' => $treatment['name']],
                $treatment
            );
        }
    }
}