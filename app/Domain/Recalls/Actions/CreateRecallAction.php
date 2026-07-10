<?php

namespace App\Domain\Recalls\Actions;

use App\Domain\Recalls\DTOs\CreateRecallDTO;
use App\Domain\Recalls\Repositories\RecallRepository;
use App\Domain\Recalls\Services\RecallService;

class CreateRecallAction
{
    public function __construct(
        private readonly RecallRepository $repository,
        private readonly RecallService $service
    ) {}

    public function execute(CreateRecallDTO $dto)
    {
        $this->service->validateDueDate($dto->dueDate);

        return $this->repository->create([
            'user_id' => $dto->userId,
            'recall_type_id' => $dto->recallTypeId,
            'due_date' => $dto->dueDate,
            'status' => $dto->status,
        ]);
    }
}
