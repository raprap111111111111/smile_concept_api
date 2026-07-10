<?php

namespace App\Domain\Invoices\DTOs;

final readonly class CreateInvoiceDTO
{
    /**
     * @param CreateInvoiceItemDTO[] $items
     */
    public function __construct(
        public int $appointmentId,
        public array $items,
        public ?string $notes = null,
        public ?string $dueDate = null,
    ) {}
}