<?php

namespace Database\Seeders;

use App\Models\RecallType;
use Illuminate\Database\Seeder;

class RecallTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'slug' => 'cleaning',
                'label' => 'Standard Teeth Cleaning (6-Month)',
                'frequency_months' => 6,
            ],
            [
                'slug' => 'follow_up',
                'label' => 'Post-Op Clinical Follow-Up',
                'frequency_months' => 1,
            ],
            [
                'slug' => 'ortho',
                'label' => 'Orthodontic Adjustment / Braces',
                'frequency_months' => 1,
            ],
            [
                'slug' => 'periodontal',
                'label' => 'Periodontal Maintenance deep scaling',
                'frequency_months' => 3,
            ]
        ];

        foreach ($types as $type) {
            RecallType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
