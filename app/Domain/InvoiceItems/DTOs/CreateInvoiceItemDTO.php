<?php

namespace App\Domain\InvoiceItems\DTOs;

final readonly class CreateInvoiceItemDTO
{
    public function __construct(
        public int $invoiceId,
        public int $treatmentId,
        public int $quantity,
        public float $discount = 0.00
    ) {}
}
