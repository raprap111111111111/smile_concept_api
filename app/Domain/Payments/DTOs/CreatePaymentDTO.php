<?php

namespace App\Domain\Payments\DTOs;

final readonly class CreatePaymentDTO
{
    public function __construct(
        public int $invoiceId,
        public float $amount,
        public string $paymentMethod,
        public string $paymentDate,
        public ?string $transactionReference = null,
        public ?string $notes = null
    ) {}
}
