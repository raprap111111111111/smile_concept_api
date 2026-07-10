<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Services\PaymentService;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DeletePaymentAction
{
    public function __construct(
        private readonly PaymentService $service,
    ) {}

    public function execute(Payment $payment): bool
    {
        if ($payment->trashed()) {
            throw new \DomainException("Payment has already been reverted.");
        }

        return DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice()->lockForUpdate()->first();

            $deleted = (bool) $payment->delete(); // soft delete

            if ($deleted && $invoice) {
                $this->service->recalculateInvoiceBalance($invoice);
            }

            return $deleted;
        });
    }
}