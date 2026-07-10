<?php

namespace App\Http\Controllers\v1;

use App\Domain\DentalCharts\Actions\CreateDentalChartAction;
use App\Domain\DentalCharts\Actions\DeleteDentalChartAction;
use App\Domain\DentalCharts\Actions\UpdateDentalChartAction;
use App\Domain\DentalCharts\Mappers\DentalChartMapper;
use App\Domain\DentalCharts\Repositories\DentalChartRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\DentalChart\DeleteDentalChartRequest;
use App\Http\Requests\v1\DentalChart\GetAllDentalChartRequest;
use App\Http\Requests\v1\DentalChart\GetDentalChartRequest;
use App\Http\Requests\v1\DentalChart\StoreDentalChartRequest;
use App\Http\Requests\v1\DentalChart\UpdateDentalChartRequest;
use App\Http\Resources\v1\DentalChartResource;
use App\Models\DentalChart;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DentalChartController extends Controller
{
    use ApiResponse;
    
    public function __construct(
        private readonly DentalChartRepository $repository,
        private readonly CreateDentalChartAction $createAction,
        private readonly UpdateDentalChartAction $updateAction,
        private readonly DeleteDentalChartAction $deleteAction
    ) {}

    public function index(GetAllDentalChartRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), DentalChartResource::class);
        return $this->responseSuccess($result, 'Patient dental charts retrieved successfully');
    }

    public function show(GetDentalChartRequest $request, DentalChart $dentalChart): JsonResponse
    {
        return $this->responseSuccess(
            new DentalChartResource($dentalChart->load(['user', 'entries.condition'])),
            'Dental chart profile found successfully'
        );
    }

    public function store(StoreDentalChartRequest $request): JsonResponse
    {
        $chart = $this->createAction->execute(
            DentalChartMapper::fromCreateRequest($request)
        );

        return $this->responseSuccess(
            new DentalChartResource($chart),
            'Dental chart entry created successfully',
            JsonResponse::HTTP_CREATED
        );
    }

    public function update(UpdateDentalChartRequest $request, DentalChart $dentalChart): JsonResponse
    {
        $updated = $this->updateAction->execute(
            $dentalChart,
            DentalChartMapper::fromUpdateRequest($request)
        );

        return $this->responseSuccess(
            new DentalChartResource($updated),
            'Dental chart record updated successfully'
        );
    }

    public function destroy(DeleteDentalChartRequest $request, DentalChart $dentalChart): JsonResponse
    {
        $this->deleteAction->execute($dentalChart);
        return $this->responseSuccess(null, 'Dental chart entry deleted successfully.');
    }
}
