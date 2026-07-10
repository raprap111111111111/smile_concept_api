<?php

namespace App\Domain\Invoices\Actions;

use App\Domain\Invoices\DTOs\RecordPaymentDTO;
use App\Domain\Invoices\Services\BillingService;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class RecordPaymentAction
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {}

    public function execute(Invoice $invoice, RecordPaymentDTO $dto): Invoice
    {
        // --- Guard clauses ---
        if ($invoice->balance_due <= 0.00) {
            throw new \DomainException("Invoice [{$invoice->invoice_number}] is already fully paid.");
        }

        if ($dto->amount <= 0.00) {
            throw new \InvalidArgumentException("Payment amount must be greater than zero.");
        }

        // ✅ Prevent overpayment
        if ($dto->amount > $invoice->balance_due) {
            throw new \DomainException(
                "Payment of {$dto->amount} exceeds outstanding balance of {$invoice->balance_due}."
            );
        }

        return DB::transaction(function () use ($invoice, $dto) {

            // 1. Log payment
            $invoice->payments()->create([
                'amount'                => $dto->amount,
                'payment_method'        => $dto->paymentMethod,
                'payment_date'          => $dto->paymentDate,
                'transaction_reference' => $dto->transactionReference,
                'notes'                 => $dto->notes,
            ]);

            // 2. Recalculate balance from DB sum (prevents float drift)
            $totalPaid  = (float) $invoice->payments()->sum('amount');
            $newBalance = max(0.00, round((float) $invoice->total_amount - $totalPaid, 2));

            // 3. Derive new status
            $newStatus  = $this->billingService->determineStatus(
                (float) $invoice->total_amount,
                $newBalance
            );

            // 4. Persist
            $invoice->update([
                'balance_due' => $newBalance,
                'status'      => $newStatus,
            ]);

            return $invoice->load(['payments', 'items.treatment']);
        });
    }
}