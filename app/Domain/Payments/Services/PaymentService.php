<?php

namespace App\Domain\Payments\Services;

use App\Domain\Invoices\Services\BillingService;
use App\Models\Invoice;

class PaymentService
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {}

    /**
     * Ensure the payment amount is valid against the invoice's outstanding balance.
     */
    public function validatePaymentAmount(Invoice $invoice, float $amount): void
    {
        if ($invoice->balance_due <= 0.00) {
            throw new \DomainException(
                "Invoice [{$invoice->invoice_number}] is already fully paid."
            );
        }

        if ($amount <= 0.00) {
            throw new \InvalidArgumentException(
                "Payment amount must be greater than zero."
            );
        }

        if ($amount > (float) $invoice->balance_due) {
            throw new \DomainException(
                "Payment of {$amount} exceeds outstanding balance of {$invoice->balance_due}."
            );
        }
    }

    /**
     * Recalculate invoice balance & status from remaining (non-deleted) payments.
     */
    public function recalculateInvoiceBalance(Invoice $invoice): Invoice
    {
        $totalPaid  = (float) $invoice->payments()->sum('amount');
        $newBalance = max(0.00, round((float) $invoice->total_amount - $totalPaid, 2));

        $newStatus  = $this->billingService->determineStatus(
            (float) $invoice->total_amount,
            $newBalance
        );

        $invoice->update([
            'balance_due' => $newBalance,
            'status'      => $newStatus,
        ]);

        return $invoice->refresh();
    }
}