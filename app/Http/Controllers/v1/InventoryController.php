<?php

namespace App\Http\Controllers\v1;

use App\Domain\Inventories\Actions\CreateInventoryAction;
use App\Domain\Inventories\Actions\DeleteInventoryAction;
use App\Domain\Inventories\Actions\UpdateInventoryAction;
use App\Domain\Inventories\Mappers\InventoryMapper;
use App\Domain\Inventories\Repositories\InventoryRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Inventory\DeleteInventoryRequest;
use App\Http\Requests\v1\Inventory\GetAllInventoriesRequest;
use App\Http\Requests\v1\Inventory\GetInventoryRequest;
use App\Http\Requests\v1\Inventory\StoreInventoryRequest;
use App\Http\Requests\v1\Inventory\UpdateInventoryRequest;
use App\Http\Resources\v1\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryRepository $repository,
        private readonly CreateInventoryAction $createAction,
        private readonly UpdateInventoryAction $updateAction,
        private readonly DeleteInventoryAction $deleteAction
    ) {}

    public function index(GetAllInventoriesRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), InventoryResource::class);
        return $this->successResponse($result, 'Branch stock catalogs retrieved.');
    }

    public function show(GetInventoryRequest $request, Inventory $inventory): JsonResponse
    {
        return $this->successResponse(
            new InventoryResource($inventory->load(['branch', 'item'])),
            'Branch stock item details loaded successfully.'
        );
    }

    public function store(StoreInventoryRequest $request): JsonResponse
    {
        try {
            $inventory = $this->createAction->execute(
                InventoryMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new InventoryResource($inventory->load(['branch', 'item'])),
                'Stock catalog added to specified branch successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $inventory,
                InventoryMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new InventoryResource($updated->load(['branch', 'item'])),
                'Branch stock record modified successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteInventoryRequest $request, Inventory $inventory): JsonResponse
    {
        $this->deleteAction->execute($inventory);
        return $this->successResponse(null, 'Branch stock record deleted successfully.');
    }
}
