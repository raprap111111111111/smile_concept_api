<?php

namespace App\Domain\Recalls\Mappers;

use App\Domain\Recalls\DTOs\CreateRecallDTO;
use App\Domain\Recalls\DTOs\UpdateRecallDTO;
use App\Http\Requests\v1\Recall\StoreRecallRequest;
use App\Http\Requests\v1\Recall\UpdateRecallRequest;

class RecallMapper
{
    public static function fromCreateRequest(StoreRecallRequest $request): CreateRecallDTO
    {
        return new CreateRecallDTO(
            userId: (int) $request->validated('user_id'),
            recallTypeId: (int) $request->validated('recall_type_id'),
            dueDate: $request->validated('due_date'),
            status: $request->validated('status', 'pending')
        );
    }

    public static function fromUpdateRequest(UpdateRecallRequest $request): UpdateRecallDTO
    {
        return new UpdateRecallDTO(
            userId: $request->validated('user_id') ? (int) $request->validated('user_id') : null,
            recallTypeId: $request->validated('recall_type_id') ? (int) $request->validated('recall_type_id') : null,
            dueDate: $request->validated('due_date'),
            status: $request->validated('status'),
            lastNotifiedAt: $request->validated('last_notified_at')
        );
    }
}
