<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentalChartEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dental_chart_id' => $this->dental_chart_id,
            'tooth_number' => $this->tooth_number,
            'tooth_condition_id' => $this->tooth_condition_id,
            'condition_slug' => $this->condition?->slug,
            'condition_label' => $this->condition?->label,
            'condition_color' => $this->condition?->color_code,
            'treatment_applied' => $this->treatment_applied,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
