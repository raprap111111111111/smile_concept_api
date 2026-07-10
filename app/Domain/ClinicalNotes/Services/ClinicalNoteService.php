<?php

namespace App\Domain\ClinicalNotes\Services;

use App\Models\ClinicalNote;

class ClinicalNoteService
{
    public function verifyLockedState(ClinicalNote $note): void
    {
        if ($note->is_locked) {
            throw new \Exception("This clinical note has been legally locked and can no longer be modified.");
        }
    }
}
