<?php

namespace App\Http\Controllers\v1;

use App\Domain\InvoiceItems\Actions\CreateInvoiceItemAction;
use App\Domain\InvoiceItems\Actions\DeleteInvoiceItemAction;
use App\Domain\InvoiceItems\Actions\UpdateInvoiceItemAction;
use App\Domain\InvoiceItems\Mappers\InvoiceItemMapper;
use App\Domain\InvoiceItems\Repositories\InvoiceItemRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\InvoiceItem\DeleteInvoiceItemRequest;
use App\Http\Requests\v1\InvoiceItem\GetAllInvoiceItemsRequest;
use App\Http\Requests\v1\InvoiceItem\GetInvoiceItemRequest;
use App\Http\Requests\v1\InvoiceItem\StoreInvoiceItemRequest;
use App\Http\Requests\v1\InvoiceItem\UpdateInvoiceItemRequest;
use App\Http\Resources\v1\InvoiceItemResource;
use App\Models\InvoiceItem;
use Illuminate\Http\JsonResponse;

class InvoiceItemController extends Controller
{
    public function __construct(
        private readonly InvoiceItemRepository $repository,
        private readonly CreateInvoiceItemAction $createAction,
        private readonly UpdateInvoiceItemAction $updateAction,
        private readonly DeleteInvoiceItemAction $deleteAction
    ) {}

    public function index(GetAllInvoiceItemsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), InvoiceItemResource::class);
        return $this->successResponse($result, 'Invoice line items retrieved.');
    }

    public function show(GetInvoiceItemRequest $request, InvoiceItem $invoiceItem): JsonResponse
    {
        return $this->successResponse(
            new InvoiceItemResource($invoiceItem->load(['invoice', 'treatment'])),
            'Invoice line item details loaded successfully.'
        );
    }

    public function store(StoreInvoiceItemRequest $request): JsonResponse
    {
        try {
            $item = $this->createAction->execute(
                InvoiceItemMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new InvoiceItemResource($item->load(['invoice', 'treatment'])),
                'Line item added and invoice values synced.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateInvoiceItemRequest $request, InvoiceItem $invoiceItem): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $invoiceItem,
                InvoiceItemMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new InvoiceItemResource($updated->load(['invoice', 'treatment'])),
                'Line item updated and invoice values resynced.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteInvoiceItemRequest $request, InvoiceItem $invoiceItem): JsonResponse
    {
        try {
            $this->deleteAction->execute($invoiceItem);
            return $this->successResponse(null, 'Line item dropped and invoice values rebalanced.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
