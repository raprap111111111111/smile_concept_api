<?php

namespace App\Http\Controllers\v1;

use App\Domain\StockMovements\Repositories\StockMovementRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Inventory\GetStockMovementsRequest;
use App\Http\Resources\v1\StockMovementResource;
use Illuminate\Http\JsonResponse;

/**
 * Read-only view of the stock ledger.
 *
 * There is no store/update/destroy on purpose: movements are written only by
 * StockLedger, and an append-only ledger that callers can edit is not a ledger.
 */
class StockMovementController extends Controller
{
    public function __construct(
        private readonly StockMovementRepository $repository,
    ) {}

    public function index(GetStockMovementsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate(
            $request->validated(),
            StockMovementResource::class,
        );

        return $this->successResponse($result, 'Stock movements retrieved.');
    }
}
