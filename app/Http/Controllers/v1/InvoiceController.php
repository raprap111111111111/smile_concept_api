<?php

namespace App\Http\Controllers\v1;

use App\Domain\Invoices\Actions\CreateInvoiceAction;
use App\Domain\Invoices\Mappers\InvoiceMapper;
use App\Domain\Invoices\Repositories\InvoiceRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Invoice\GetAllInvoicesRequest;
use App\Http\Requests\v1\Invoice\GetInvoiceRequest;
use App\Http\Requests\v1\Invoice\StoreInvoiceRequest;
use App\Http\Resources\v1\InvoiceResource;
use App\Models\Invoice;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly InvoiceRepository   $repository,
        private readonly CreateInvoiceAction $createAction,
    ) {}

    /**
     * List all invoices with filters & pagination.
     */
    public function index(GetAllInvoicesRequest $request): JsonResponse
    {
        $result = $this->repository->paginate(
            $request->validated(),
            InvoiceResource::class
        );

        return $this->successResponse($result, 'Invoices register parsed successfully.');
    }

    /**
     * Show a single invoice with its items and payments.
     */
    public function show(GetInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        return $this->successResponse(
            new InvoiceResource($invoice->load(['items.treatment', 'payments'])),
            'Invoice profile retrieved successfully.'
        );
    }

    /**
     * Generate a new invoice for an appointment.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = $this->createAction->execute(
                InvoiceMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new InvoiceResource($invoice),
                'Invoice generated and calculated.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}