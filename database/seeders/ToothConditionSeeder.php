<?php

namespace Database\Seeders;

use App\Models\ToothCondition;
use Illuminate\Database\Seeder;

class ToothConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            [
                'slug' => 'healthy',
                'label' => 'Healthy',
                'color_code' => '#2ECC71',
            ],
            [
                'slug' => 'decayed',
                'label' => 'Decayed (Caries)',
                'color_code' => '#E74C3C',
            ],
            [
                'slug' => 'missing',
                'label' => 'Missing',
                'color_code' => '#95A5A6',
            ],
            [
                'slug' => 'filled',
                'label' => 'Filled (Restored)',
                'color_code' => '#3498DB',
            ],
            [
                'slug' => 'crowned',
                'label' => 'Crowned',
                'color_code' => '#F1C40F',
            ],
            [
                'slug' => 'implant',
                'label' => 'Dental Implant',
                'color_code' => '#9B59B6',
            ],
            [
                'slug' => 'root_canal',
                'label' => 'Root Canal Treated',
                'color_code' => '#34495E',
            ],
            [
                'slug' => 'fractured',
                'label' => 'Fractured Tooth',
                'color_code' => '#E67E22',
            ],
            [
                'slug' => 'impacted',
                'label' => 'Impacted Tooth',
                'color_code' => '#1ABC9C',
            ]
        ];

        foreach ($conditions as $condition) {
            ToothCondition::updateOrCreate(
                ['slug' => $condition['slug']],
                $condition
            );
        }
    }
}
