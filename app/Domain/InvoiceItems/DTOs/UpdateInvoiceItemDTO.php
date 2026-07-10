<?php

namespace App\Domain\InvoiceItems\DTOs;

final readonly class UpdateInvoiceItemDTO
{
    public function __construct(
        public ?int $invoiceId = null,
        public ?int $treatmentId = null,
        public ?int $quantity = null,
        public ?float $discount = null
    ) {}
}
