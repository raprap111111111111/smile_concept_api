<?php

namespace App\Domain\Recalls\Actions;

use App\Domain\Recalls\DTOs\UpdateRecallDTO;
use App\Domain\Recalls\Repositories\RecallRepository;
use App\Domain\Recalls\Services\RecallService;
use App\Models\Recall;

class UpdateRecallAction
{
    public function __construct(
        private readonly RecallRepository $repository,
        private readonly RecallService $service
    ) {}

    public function execute(Recall $recall, UpdateRecallDTO $dto)
    {
        if ($dto->dueDate !== null) {
            $this->service->validateDueDate($dto->dueDate);
        }

        $data = array_filter([
            'user_id' => $dto->userId,
            'recall_type_id' => $dto->recallTypeId,
            'due_date' => $dto->dueDate,
            'status' => $dto->status,
            'last_notified_at' => $dto->lastNotifiedAt,
        ], fn($value) => !is_null($value));

        return $this->repository->update($recall, $data);
    }
}
