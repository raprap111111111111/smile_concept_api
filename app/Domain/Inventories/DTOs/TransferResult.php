<?php

namespace App\Domain\Inventories\DTOs;

/** Both halves of a branch-to-branch move. */
final readonly class TransferResult
{
    /** @param list<MovementResult> $inbound One per source lot the move drew from. */
    public function __construct(
        public MovementResult $outbound,
        public array $inbound,
        public int $quantity,
        public int $sourceBalance,
        public int $destinationBalance,
    ) {}
}
