<?php
// DeletePatientAttachmentAction.php
namespace App\Domain\PatientAttachments\Actions;

use App\Models\PatientAttachment;

class DeletePatientAttachmentAction
{
    public function execute(PatientAttachment $attachment): void
    {
        $attachment->delete();
    }
}