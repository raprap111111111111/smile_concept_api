<?php

namespace App\Domain\DentalChartEntries\Actions;

use App\Domain\DentalChartEntries\Repositories\DentalChartEntryRepository;
use App\Models\DentalChartEntry;

class DeleteDentalChartEntryAction
{
    public function __construct(
        private readonly DentalChartEntryRepository $repository
    ) {}

    public function execute(DentalChartEntry $entry): bool
    {
        return $this->repository->delete($entry);
    }
}
