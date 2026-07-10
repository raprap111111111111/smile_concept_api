<?php

namespace Database\Seeders;

use App\Models\ConsentTemplate;
use Illuminate\Database\Seeder;

class ConsentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'title' => 'Consent for General Dental Treatment',
                'body' => 'I hereby authorize the clinical staff of Smile Concept to perform diagnostic examinations, dental cleanings, restorative fillings, and local anesthesia as deemed necessary for my oral health. I understand that clinical outcomes cannot be guaranteed and that I have the right to ask questions regarding treatment alternatives before procedures begin.',
                'is_active' => true,
            ],
            [
                'title' => 'Informed Consent for Oral Surgery and Tooth Extraction',
                'body' => 'I consent to the extraction of the designated teeth by my prescribing dentist. I have been informed of the potential risks associated with oral surgeries, including but not limited to post-operative pain, bleeding, swelling, dry socket (alveolar osteitis), and temporary or permanent numbness of the lip, chin, or tongue due to proximity to nerve structures. I agree to strictly follow all post-operative care instructions.',
                'is_active' => true,
            ],
            [
                'title' => 'Consent for In-Chair Teeth Whitening Procedure',
                'body' => 'I authorize the cosmetic application of laser/chemical teeth whitening agents. I understand that teeth whitening treatments are cosmetic in nature and results vary based on individual enamel density and habits. I have been informed of common temporary side effects, specifically mild to moderate tooth sensitivity and gum irritation, which typically resolve within 48 hours post-procedure.',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            ConsentTemplate::updateOrCreate(
                ['title' => $template['title']],
                $template
            );
        }
    }
}
