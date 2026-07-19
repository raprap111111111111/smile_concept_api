<?php

namespace App\Domain\PatientAttachments\Services;

use App\Models\PatientAttachment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class XrayAnalysisService
{
    /**
     * Analyze an X-ray attachment.
     * Uses mock data if no AI provider is configured.
     */
    public function analyze(PatientAttachment $attachment): void
    {
        $attachment->update(['scan_status' => 'processing']);

        try {
            // ✅ Use mock if no API key configured
            if (empty(config('services.dental_ai.key'))) {
                $result = $this->mockScan();
            } else {
                $result = $this->realScan($attachment);
            }

            $attachment->update([
                'scan_status'         => $result['status'],
                'scan_confidence'     => $result['confidence'],
                'scan_provider'       => $result['provider'],
                'scanned_at'          => now(),
                'detected_conditions' => $result['conditions'],
            ]);
        } catch (\Exception $e) {
            $attachment->update(['scan_status' => 'failed']);
            Log::error('Xray scan exception', [
                'attachment_id' => $attachment->id,
                'error'         => $e->getMessage(),
            ]);
        }
    }

    // ─── 🧪 MOCK (for development) ─────────────────────────
    private function mockScan(): array
    {
        // Simulate AI thinking time (optional)
        // sleep(2);

        return [
            'status'     => 'completed',
            'confidence' => 92.5,
            'provider'   => 'MockAI-v1',
            'conditions' => [
                [
                    'tooth_number' => 14,
                    'condition'    => 'cavity',
                    'severity'     => 'moderate',
                    'confidence'   => 88.4,
                    'location'     => 'occlusal surface',
                    'description'  => 'Detected early-stage cavity on upper left molar.',
                ],
                [
                    'tooth_number' => 30,
                    'condition'    => 'plaque_buildup',
                    'severity'     => 'mild',
                    'confidence'   => 75.1,
                    'location'     => 'lower right molar',
                    'description'  => 'Mild plaque buildup detected.',
                ],
            ],
        ];
    }

    // ─── 🚀 REAL AI (for production) ───────────────────────
    private function realScan(PatientAttachment $attachment): array
    {
        $imageContent = Storage::disk('public')->get($attachment->file_path);
        $base64 = base64_encode($imageContent);

        $baseUrl = rtrim(config('services.dental_ai.url'), '/');
        $model   = config('services.dental_ai.model');  // dentalxray-s3wqb/2
        $apiKey  = config('services.dental_ai.key');

        $url = "{$baseUrl}/{$model}?api_key={$apiKey}";

        Log::info('🧠 Sending X-ray to Roboflow Docker', [
            'model' => $model,
            'file'  => $attachment->file_name,
            'url'   => $baseUrl,
        ]);

        $response = Http::timeout(120)
            ->withBody($base64, 'application/x-www-form-urlencoded')
            ->post($url);

        if (!$response->successful()) {
            Log::error('❌ Roboflow scan failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('AI scan failed: ' . $response->body());
        }

        $data        = $response->json();
        $predictions = $data['predictions'] ?? [];

        Log::info('✅ Roboflow returned detections', [
            'count'   => count($predictions),
            'classes' => collect($predictions)->pluck('class')->unique()->values(),
            'raw'     => $predictions, // Remove after debugging
        ]);

        $conditions = collect($predictions)
            ->filter(fn($p) => ($p['confidence'] ?? 0) >= 0.25)
            ->map(function ($pred) use ($data) {
                $confidence  = ($pred['confidence'] ?? 0) * 100;
                $class       = $pred['class'] ?? 'unknown';
                $toothNumber = $this->mapClassToToothNumber($class, $pred, $data);

                return [
                    'tooth_number' => $toothNumber,
                    'condition'    => $this->mapClassToCondition($class),
                    'severity'     => $this->calculateSeverity($confidence),
                    'confidence'   => round($confidence, 1),
                    'location'     => sprintf(
                        'x:%d, y:%d (%dx%d)',
                        (int)($pred['x'] ?? 0),
                        (int)($pred['y'] ?? 0),
                        (int)($pred['width'] ?? 0),
                        (int)($pred['height'] ?? 0),
                    ),
                    'description' => sprintf(
                        'AI identified %s with %.1f%% confidence.',
                        ucwords(str_replace('_', ' ', $class)),
                        $confidence
                    ),
                ];
            })
            ->values()
            ->toArray();

        $avgConfidence = count($conditions) > 0
            ? collect($conditions)->avg('confidence')
            : 0;

        return [
            'status'     => 'completed',
            'confidence' => round($avgConfidence, 1),
            'provider'   => 'Roboflow: ' . $model,
            'conditions' => $conditions,
        ];
    }

    // ─── Helper Methods ──────────────────────────────────────────────

    private function mapClassToToothNumber(string $class, array $pred, array $data): int
    {
        // If class is already a number (0-11), convert to FDI-like tooth number
        if (is_numeric($class)) {
            $num = (int)$class;
            // Map 0-11 to tooth positions 1-32
            return ($num * 2) + 11;
        }

        // For named classes, estimate from X position
        $x          = $pred['x'] ?? 0;
        $imageWidth = $data['image']['width'] ?? 800;
        $normalized = min(1.0, max(0.0, $x / $imageWidth));

        return (int) round($normalized * 31) + 1;
    }

    private function mapClassToCondition(string $class): string
    {
        $map = [
            // Anatomy → clinical relevance
            'molar'              => 'molar_tooth',
            'premolar'           => 'premolar_tooth',
            'canine'             => 'canine_tooth',
            'central incisor'    => 'central_incisor',
            'cental incisor'     => 'central_incisor',   // typo in dataset
            'lateral incisor'    => 'lateral_incisor',
            'lareral incisor'    => 'lateral_incisor',   // typo in dataset
            'mandible'           => 'mandibular_region',
            'maxilla'            => 'maxillary_region',
            'object'             => 'dental_finding',

            // Conditions
            'caries'             => 'cavity',
            'cavity'             => 'cavity',
            'decay'              => 'cavity',
            'filling'            => 'existing_filling',
            'crown'              => 'existing_crown',
            'implant'            => 'dental_implant',
            'root'               => 'root_canal_needed',
            'fracture'           => 'tooth_fracture',
            'impacted'           => 'wisdom_tooth_impaction',
        ];

        $lower = strtolower(trim($class));

        foreach ($map as $key => $value) {
            if (str_contains($lower, $key)) {
                return $value;
            }
        }

        return strtolower(str_replace([' ', '-'], '_', $class));
    }

    private function calculateSeverity(float $confidence): string
    {
        if ($confidence >= 85) return 'severe';
        if ($confidence >= 65) return 'moderate';
        return 'mild';
    }
}
