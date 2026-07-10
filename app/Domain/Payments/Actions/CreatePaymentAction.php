<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\DTOs\CreatePaymentDTO;
use App\Domain\Payments\Repositories\PaymentRepository;
use App\Domain\Payments\Services\PaymentService;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\InvoicePaidNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreatePaymentAction
{
    public function __construct(
        private readonly PaymentRepository $repository,
        private readonly PaymentService    $service,
    ) {}

    public function execute(CreatePaymentDTO $dto): Payment
    {
        // ─── 1. Create payment in transaction ─────────────────
        $payment = DB::transaction(function () use ($dto) {

            // Lock invoice to prevent concurrent overpayment
            $invoice = Invoice::lockForUpdate()->findOrFail($dto->invoiceId);

            // Validate amount
            $this->service->validatePaymentAmount($invoice, $dto->amount);

            // Log payment entry
            $payment = $this->repository->create([
                'invoice_id'            => $dto->invoiceId,
                'amount'                => $dto->amount,
                'payment_method'        => $dto->paymentMethod,
                'payment_date'          => $dto->paymentDate,
                'transaction_reference' => $dto->transactionReference,
                'notes'                 => $dto->notes,
            ]);

            // Recalculate invoice balance
            $this->service->recalculateInvoiceBalance($invoice);

            return $payment->load('invoice');
        });

        // ─── 2. Reload with all relations ─────────────────────
        $payment->load('invoice.appointment.user');

        // ─── 3. Check if invoice is fully paid ────────────────
        $this->sendPaymentNotifications($payment);

        return $payment;
    }

    /**
     * Fire notifications if invoice has been fully settled.
     */
    private function sendPaymentNotifications(Payment $payment): void
    {
        $invoice = $payment->invoice;

        // Only notify when invoice is now fully paid
        if ($invoice->status !== InvoiceStatus::PAID) {
            return;
        }

        $patient = $invoice->appointment?->user;

        if (!$patient) {
            return;
        }

        $notification = new InvoicePaidNotification($invoice);

        // Notify the patient (email + bell)
        $patient->notify($notification);

        // Notify admins (bell only, for tracking revenue)
        $admins = $this->getAdmins();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notification);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function getAdmins(): Collection
    {
        return User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->get();
    }
}