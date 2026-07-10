<?php

namespace App\Domain\LabCases\Actions;

use App\Domain\LabCases\DTOs\CreateLabCaseDTO;
use App\Domain\LabCases\Repositories\LabCaseRepository;
use App\Domain\LabCases\Services\LabCaseService;

class CreateLabCaseAction
{
    public function __construct(
        private readonly LabCaseRepository $repository,
        private readonly LabCaseService $service
    ) {}

    public function execute(CreateLabCaseDTO $dto)
    {
        $this->service->validateDates($dto->sentDate, $dto->dueDate);

        return $this->repository->create([
            'appointment_id' => $dto->appointmentId,
            'lab_name' => $dto->labName,
            'work_type' => $dto->workType,
            'status' => $dto->status,
            'sent_date' => $dto->sentDate,
            'due_date' => $dto->dueDate,
            'received_date' => $dto->receivedDate,
            'cost' => $dto->cost,
            'notes' => $dto->notes,
        ]);
    }
}
