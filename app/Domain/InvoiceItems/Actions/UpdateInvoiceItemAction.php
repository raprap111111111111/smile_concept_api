<?php

namespace App\Domain\InvoiceItems\Actions;

use App\Domain\InvoiceItems\DTOs\UpdateInvoiceItemDTO;
use App\Domain\InvoiceItems\Repositories\InvoiceItemRepository;
use App\Domain\InvoiceItems\Services\InvoiceItemService;
use App\Domain\Invoices\Services\BillingService;
use App\Models\InvoiceItem;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class UpdateInvoiceItemAction
{
    public function __construct(
        private readonly InvoiceItemRepository $repository,
        private readonly InvoiceItemService $service,
        private readonly BillingService $billingService
    ) {}

    public function execute(InvoiceItem $item, UpdateInvoiceItemDTO $dto)
    {
        $invoice = $item->invoice;
        
        $treatmentId = $dto->treatmentId ?? $item->treatment_id;
        $treatment = Treatment::findOrFail($treatmentId);

        $quantity = $dto->quantity ?? $item->quantity;
        $discount = $dto->discount ?? (float) $item->discount;
        $unitPrice = $treatment->price;

        $totalPrice = $this->billingService->calculateItemTotal(
            (float) $unitPrice,
            $quantity,
            $discount
        );

        return DB::transaction(function () use ($item, $invoice, $treatmentId, $quantity, $unitPrice, $discount, $totalPrice) {
            // 1. Update Line Item
            $this->repository->update($item, [
                'treatment_id' => $treatmentId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'total_price' => $totalPrice,
            ]);

            // 2. Sync totals to parent invoice
            $this->service->syncParentInvoiceTotals($invoice);

            return $item->load('invoice');
        });
    }
}
