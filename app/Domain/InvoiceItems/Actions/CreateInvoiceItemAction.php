<?php

namespace App\Domain\InvoiceItems\Actions;

use App\Domain\InvoiceItems\DTOs\CreateInvoiceItemDTO;
use App\Domain\InvoiceItems\Repositories\InvoiceItemRepository;
use App\Domain\InvoiceItems\Services\InvoiceItemService;
use App\Domain\Invoices\Services\BillingService;
use App\Models\Invoice;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class CreateInvoiceItemAction
{
    public function __construct(
        private readonly InvoiceItemRepository $repository,
        private readonly InvoiceItemService $service,
        private readonly BillingService $billingService
    ) {}

    public function execute(CreateInvoiceItemDTO $dto)
    {
        $invoice = Invoice::findOrFail($dto->invoiceId);
        $treatment = Treatment::findOrFail($dto->treatmentId);

        // Calculate line total: (unit_price * quantity) - discount
        $totalPrice = $this->billingService->calculateItemTotal(
            (float) $treatment->price,
            $dto->quantity,
            $dto->discount
        );

        return DB::transaction(function () use ($invoice, $treatment, $dto, $totalPrice) {
            // 1. Save Line Item record
            $item = $this->repository->create([
                'invoice_id' => $dto->invoiceId,
                'treatment_id' => $dto->treatmentId,
                'quantity' => $dto->quantity,
                'unit_price' => $treatment->price,
                'discount' => $dto->discount,
                'total_price' => $totalPrice,
            ]);

            // 2. Sync totals to parent invoice
            $this->service->syncParentInvoiceTotals($invoice);

            return $item->load('invoice');
        });
    }
}
