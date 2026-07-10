<?php

namespace App\Domain\PatientAttachments\Actions;

use App\Domain\PatientAttachments\Repositories\PatientAttachmentRepository;
use App\Models\PatientAttachment;

class DeletePatientAttachmentAction
{
    public function __construct(
        private readonly PatientAttachmentRepository $repository
    ) {}

    public function execute(PatientAttachment $attachment): bool
    {
        return $this->repository->delete($attachment);
    }
}
