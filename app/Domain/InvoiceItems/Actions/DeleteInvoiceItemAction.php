<?php

namespace App\Domain\InvoiceItems\Actions;

use App\Domain\InvoiceItems\Repositories\InvoiceItemRepository;
use App\Domain\InvoiceItems\Services\InvoiceItemService;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class DeleteInvoiceItemAction
{
    public function __construct(
        private readonly InvoiceItemRepository $repository,
        private readonly InvoiceItemService $service
    ) {}

    public function execute(InvoiceItem $item): bool
    {
        return DB::transaction(function () use ($item) {
            $invoice = $item->invoice;

            // 1. Delete the item
            $deleted = $this->repository->delete($item);

            if ($deleted && $invoice) {
                // 2. Sync totals to parent invoice
                $this->service->syncParentInvoiceTotals($invoice);
            }

            return $deleted;
        });
    }
}
