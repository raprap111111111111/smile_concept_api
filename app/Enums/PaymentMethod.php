<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH          = 'cash';
    case CARD          = 'card';
    case BANK_TRANSFER = 'bank_transfer';
    case INSURANCE     = 'insurance';
    case GCASH         = 'gcash';
    case MAYA          = 'maya';

    public function label(): string
    {
        return match($this) {
            self::CASH          => 'Cash',
            self::CARD          => 'Credit / Debit Card',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::INSURANCE     => 'Insurance',
            self::GCASH         => 'GCash',
            self::MAYA          => 'Maya',
        };
    }
}