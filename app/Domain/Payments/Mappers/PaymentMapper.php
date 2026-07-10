<?php

namespace App\Domain\Payments\Mappers;

use App\Domain\Payments\DTOs\CreatePaymentDTO;
use App\Domain\Payments\DTOs\UpdatePaymentDTO;
use App\Http\Requests\v1\Payment\StorePaymentRequest;
use App\Http\Requests\v1\Payment\UpdatePaymentRequest;

class PaymentMapper
{
    public static function fromCreateRequest(StorePaymentRequest $request): CreatePaymentDTO
    {
        return new CreatePaymentDTO(
            invoiceId: (int) $request->validated('invoice_id'),
            amount: (float) $request->validated('amount'),
            paymentMethod: $request->validated('payment_method'),
            paymentDate: $request->validated('payment_date', now()->toDateTimeString()),
            transactionReference: $request->validated('transaction_reference'),
            notes: $request->validated('notes')
        );
    }

    public static function fromUpdateRequest(UpdatePaymentRequest $request): UpdatePaymentDTO
    {
        return new UpdatePaymentDTO(
            invoiceId: $request->validated('invoice_id') ? (int) $request->validated('invoice_id') : null,
            amount: $request->has('amount') ? (float) $request->validated('amount') : null,
            paymentMethod: $request->validated('payment_method'),
            paymentDate: $request->validated('payment_date'),
            transactionReference: $request->validated('transaction_reference'),
            notes: $request->validated('notes')
        );
    }
}
