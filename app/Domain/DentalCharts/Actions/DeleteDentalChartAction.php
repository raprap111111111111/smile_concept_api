<?php

namespace App\Domain\DentalCharts\Actions;

use App\Domain\DentalCharts\Repositories\DentalChartRepository;
use App\Models\DentalChart;

class DeleteDentalChartAction
{
    public function __construct(
        private readonly DentalChartRepository $repository
    ) {}

    public function execute(DentalChart $branch): bool
    {
        return $this->repository->delete($branch);
    }
}