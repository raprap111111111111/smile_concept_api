<?php

namespace App\Domain\InvoiceItems\Mappers;

use App\Domain\InvoiceItems\DTOs\CreateInvoiceItemDTO;
use App\Domain\InvoiceItems\DTOs\UpdateInvoiceItemDTO;
use App\Http\Requests\v1\InvoiceItem\StoreInvoiceItemRequest;
use App\Http\Requests\v1\InvoiceItem\UpdateInvoiceItemRequest;

class InvoiceItemMapper
{
    public static function fromCreateRequest(StoreInvoiceItemRequest $request): CreateInvoiceItemDTO
    {
        return new CreateInvoiceItemDTO(
            invoiceId: (int) $request->validated('invoice_id'),
            treatmentId: (int) $request->validated('treatment_id'),
            quantity: (int) $request->validated('quantity'),
            discount: (float) $request->validated('discount', 0.00)
        );
    }

    public static function fromUpdateRequest(UpdateInvoiceItemRequest $request): UpdateInvoiceItemDTO
    {
        return new UpdateInvoiceItemDTO(
            invoiceId: $request->validated('invoice_id') ? (int) $request->validated('invoice_id') : null,
            treatmentId: $request->validated('treatment_id') ? (int) $request->validated('treatment_id') : null,
            quantity: $request->has('quantity') ? (int) $request->validated('quantity') : null,
            discount: $request->has('discount') ? (float) $request->validated('discount') : null
        );
    }
}
