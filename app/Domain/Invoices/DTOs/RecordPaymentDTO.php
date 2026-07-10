<?php

namespace App\Domain\Invoices\DTOs;

final readonly class RecordPaymentDTO
{
    public function __construct(
        public float   $amount,
        public string  $paymentMethod,
        public string  $paymentDate,
        public ?string $transactionReference = null,
        public ?string $notes = null,
    ) {}
}