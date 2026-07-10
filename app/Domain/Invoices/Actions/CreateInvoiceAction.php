<?php

namespace App\Domain\Invoices\Actions;

use App\Domain\Invoices\DTOs\CreateInvoiceDTO;
use App\Domain\Invoices\Repositories\InvoiceRepository;
use App\Domain\Invoices\Services\BillingService;
use App\Enums\InvoiceStatus;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    public function __construct(
        private readonly InvoiceRepository $repository,
        private readonly BillingService    $billingService,
    ) {}

    public function execute(CreateInvoiceDTO $dto): \App\Models\Invoice
    {
        return DB::transaction(function () use ($dto) {

            $totalAmount = 0.00;
            $itemsData   = [];

            // 1. Validate treatments and compute line totals
            foreach ($dto->items as $item) {
                $treatment = Treatment::findOrFail($item->treatmentId);

                $lineTotal    = $this->billingService->calculateItemTotal(
                    (float) $treatment->price,
                    $item->quantity,
                    $item->discount,
                );

                $totalAmount += $lineTotal;

                $itemsData[] = [
                    'treatment_id' => $item->treatmentId,
                    'quantity'     => $item->quantity,
                    'unit_price'   => (float) $treatment->price,
                    'discount'     => $item->discount,
                    'total_price'  => $lineTotal,
                ];
            }

            $totalAmount = round($totalAmount, 2);

            // 2. Create parent invoice
            $invoice = $this->repository->create([
                'appointment_id'  => $dto->appointmentId,
                'invoice_number'  => $this->billingService->generateInvoiceNumber(), 
                'total_amount'    => $totalAmount,
                'balance_due'     => $totalAmount,
                'status'          => InvoiceStatus::UNPAID,
                'notes'           => $dto->notes,         
                'due_date'        => $dto->dueDate,      
            ]);

            // 3. Bulk insert line items for performance
            $invoice->items()->createMany($itemsData);     
            return $invoice->load(['items.treatment']);
        });
    }
}