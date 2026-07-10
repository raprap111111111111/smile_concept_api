<?php

namespace App\Domain\ClinicalNotes\Actions;

use App\Domain\ClinicalNotes\Repositories\ClinicalNoteRepository;
use App\Domain\ClinicalNotes\Services\ClinicalNoteService;
use App\Models\ClinicalNote;

class DeleteClinicalNoteAction
{
    public function __construct(
        private readonly ClinicalNoteRepository $repository,
        private readonly ClinicalNoteService $service
    ) {}

    public function execute(ClinicalNote $note): bool
    {
        $this->service->verifyLockedState($note);
        return $this->repository->delete($note);
    }
}
