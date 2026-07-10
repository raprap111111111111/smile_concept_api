<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case VOID = 'void';

    public function label(): string
    {
        return match($this) {
            self::UNPAID => 'Unpaid',
            self::PARTIAL => 'Partial Paid',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
            self::VOID => 'Voided',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::UNPAID => '#E74C3C',          // Red
            self::PARTIAL => '#F1C40F',   // Yellow
            self::PAID => '#2ECC71',            // Green
            self::REFUNDED => '#9B59B6',        // Purple
            self::VOID => '#95A5A6',            // Gray
        };
    }
}
