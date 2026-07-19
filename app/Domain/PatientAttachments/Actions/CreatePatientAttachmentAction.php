<?php

namespace App\Domain\PatientAttachments\Actions;

use App\Domain\PatientAttachments\DTOs\CreatePatientAttachmentDTO;
use App\Domain\PatientAttachments\Services\XrayAnalysisService;
use App\Models\PatientAttachment;

class CreatePatientAttachmentAction
{
    public function __construct(
        private readonly XrayAnalysisService $xrayAnalyzer
    ) {}

    public function execute(CreatePatientAttachmentDTO $dto): PatientAttachment
    {
        $attachment = PatientAttachment::create([
            'user_id'        => $dto->userId,
            'appointment_id' => $dto->appointmentId,
            'file_name'      => $dto->fileName,
            'file_path'      => $dto->filePath,
            'file_type'      => $dto->fileType,
            'category'       => $dto->category,
            'is_xray'        => $dto->isXray,
            'notes'          => $dto->notes,
            'scan_status'    => $dto->isXray ? 'pending' : 'not_applicable',
        ]);

        // ✅ Run analysis instantly during upload
        if ($dto->isXray) {
            $this->xrayAnalyzer->analyze($attachment);
            $attachment->refresh(); // reload updated data
        }

        return $attachment->load('patient');
    }
}