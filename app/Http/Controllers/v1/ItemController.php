<?php

namespace App\Http\Controllers\v1;

use App\Domain\Items\Actions\CreateItemAction;
use App\Domain\Items\Actions\DeleteItemAction;
use App\Domain\Items\Actions\UpdateItemAction;
use App\Domain\Items\Mappers\ItemMapper;
use App\Domain\Items\Repositories\ItemRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Item\DeleteItemRequest;
use App\Http\Requests\v1\Item\GetAllItemsRequest;
use App\Http\Requests\v1\Item\GetItemRequest;
use App\Http\Requests\v1\Item\StoreItemRequest;
use App\Http\Requests\v1\Item\UpdateItemRequest;
use App\Http\Resources\v1\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    public function __construct(
        private readonly ItemRepository $repository,
        private readonly CreateItemAction $createAction,
        private readonly UpdateItemAction $updateAction,
        private readonly DeleteItemAction $deleteAction
    ) {}

    public function index(GetAllItemsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), ItemResource::class);
        return $this->successResponse($result, 'Inventory stock items retrieved.');
    }

    public function show(GetItemRequest $request, Item $item): JsonResponse
    {
        return $this->successResponse(
            new ItemResource($item),
            'Stock catalog profile details fetched successfully.'
        );
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        try {
            $item = $this->createAction->execute(
                ItemMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new ItemResource($item),
                'Stock item added to global registry.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $item,
                ItemMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new ItemResource($updated),
                'Stock catalog item modified successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteItemRequest $request, Item $item): JsonResponse
    {
        try {
            $this->deleteAction->execute($item);
            return $this->successResponse(null, 'Stock catalog item deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 409);
        }
    }
}
