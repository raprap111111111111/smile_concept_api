<?php
namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'appointment_id' => $this->appointment_id,
            'file_name'      => $this->file_name,
            'file_path'      => $this->file_path,
            'file_type'      => $this->file_type,
            'category'       => $this->category,
            'notes'          => $this->notes,

            // ✅ AI Scan
            'is_xray'             => $this->is_xray,
            'scan_status'         => $this->scan_status,
            'detected_conditions' => $this->detected_conditions,
            'scan_confidence'     => $this->scan_confidence,
            'scanned_at'          => $this->scanned_at,
            'scan_provider'       => $this->scan_provider,

            'patient' => [
                'id'   => $this->patient?->id,
                'name' => $this->patient?->name,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}