<?php

namespace App\Domain\Invoices\Mappers;

use App\Domain\Invoices\DTOs\CreateInvoiceDTO;
use App\Domain\Invoices\DTOs\CreateInvoiceItemDTO;
use App\Http\Requests\v1\Invoice\StoreInvoiceRequest;

class InvoiceMapper
{
    public static function fromCreateRequest(StoreInvoiceRequest $request): CreateInvoiceDTO
    {
        $items = array_map(
            fn($item) => new CreateInvoiceItemDTO(
                treatmentId: (int)   $item['treatment_id'],
                quantity:    (int)   $item['quantity'],
                discount:    (float) ($item['discount'] ?? 0.00),
            ),
            $request->validated('items', [])
        );

        return new CreateInvoiceDTO(
            appointmentId: (int) $request->validated('appointment_id'),
            items:         $items,
            notes:         $request->validated('notes'),
            dueDate:       $request->validated('due_date'),
        );
    }
}