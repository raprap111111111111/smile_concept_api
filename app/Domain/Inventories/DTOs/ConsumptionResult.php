<?php

namespace App\Domain\Inventories\DTOs;

/**
 * What an appointment's supply deduction did.
 *
 * Never carries a failure: deduction cannot fail in a way that matters to the
 * caller, because it is not allowed to block the appointment. A supply that ran
 * short comes back as a shortfall entry, not an exception.
 */
final readonly class ConsumptionResult
{
    /**
     * @param list<MovementResult> $movements One per item deducted.
     * @param array<int, int> $shortfalls item_id => units that could not be met.
     */
    public function __construct(
        public array $movements,
        public array $shortfalls,
        public bool $skipped,
        public ?string $skippedReason = null,
    ) {}

    public static function skipped(string $reason): self
    {
        return new self([], [], true, $reason);
    }

    public function hasShortfall(): bool
    {
        return $this->shortfalls !== [];
    }

    public function itemsDeducted(): int
    {
        return count($this->movements);
    }
}
