<?php

namespace App\Domain\Payments\DTOs;

final readonly class UpdatePaymentDTO
{
    public function __construct(
        public ?int $invoiceId = null,
        public ?float $amount = null,
        public ?string $paymentMethod = null,
        public ?string $paymentDate = null,
        public ?string $transactionReference = null,
        public ?string $notes = null
    ) {}
}
