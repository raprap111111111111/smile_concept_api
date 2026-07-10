<?php

namespace App\Http\Controllers\v1;

use App\Domain\Payments\Actions\CreatePaymentAction;
use App\Domain\Payments\Actions\DeletePaymentAction;
use App\Domain\Payments\Mappers\PaymentMapper;
use App\Domain\Payments\Repositories\PaymentRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Payment\DeletePaymentRequest;
use App\Http\Requests\v1\Payment\GetAllPaymentsRequest;
use App\Http\Requests\v1\Payment\GetPaymentRequest;
use App\Http\Requests\v1\Payment\StorePaymentRequest;
use App\Http\Resources\v1\PaymentResource;
use App\Models\Payment;
use App\Traits\ApiResponse;                             
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    use ApiResponse;                                

    public function __construct(
        private readonly PaymentRepository   $repository,
        private readonly CreatePaymentAction $createAction,
        private readonly DeletePaymentAction $deleteAction,
    ) {}

    public function index(GetAllPaymentsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), PaymentResource::class);
        return $this->successResponse($result, 'Payments logs parsed successfully.');
    }

    public function show(GetPaymentRequest $request, Payment $payment): JsonResponse
    {
        return $this->successResponse(
            new PaymentResource($payment->load('invoice')),
            'Payment details retrieved successfully.'
        );
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->createAction->execute(
                PaymentMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new PaymentResource($payment),
                'Payment successfully submitted and applied.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeletePaymentRequest $request, Payment $payment): JsonResponse
    {
        try {
            $this->deleteAction->execute($payment);
            return $this->successResponse(null, 'Payment entry reverted successfully.');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}