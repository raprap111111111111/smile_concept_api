<?php

namespace App\Domain\LabCases\Actions;

use App\Domain\LabCases\DTOs\UpdateLabCaseDTO;
use App\Domain\LabCases\Repositories\LabCaseRepository;
use App\Domain\LabCases\Services\LabCaseService;
use App\Models\LabCase;

class UpdateLabCaseAction
{
    public function __construct(
        private readonly LabCaseRepository $repository,
        private readonly LabCaseService $service
    ) {}

    public function execute(LabCase $labCase, UpdateLabCaseDTO $dto)
    {
        $sentDate = $dto->sentDate ?? $labCase->sent_date->toDateString();
        $dueDate = $dto->dueDate ?? $labCase->due_date->toDateString();

        $this->service->validateDates($sentDate, $dueDate);

        $data = array_filter([
            'appointment_id' => $dto->appointmentId,
            'lab_name' => $dto->labName,
            'work_type' => $dto->workType,
            'status' => $dto->status,
            'sent_date' => $dto->sentDate,
            'due_date' => $dto->dueDate,
            'received_date' => $dto->receivedDate,
            'cost' => $dto->cost,
            'notes' => $dto->notes,
        ], fn($value) => !is_null($value));

        return $this->repository->update($labCase, $data);
    }
}
