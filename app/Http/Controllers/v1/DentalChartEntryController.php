<?php

namespace App\Http\Controllers\v1;

use App\Domain\DentalChartEntries\Actions\CreateDentalChartEntryAction;
use App\Domain\DentalChartEntries\Actions\DeleteDentalChartEntryAction;
use App\Domain\DentalChartEntries\Actions\UpdateDentalChartEntryAction;
use App\Domain\DentalChartEntries\Mappers\DentalChartEntryMapper;
use App\Domain\DentalChartEntries\Repositories\DentalChartEntryRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\DentalChartEntry\DeleteDentalChartEntryRequest;
use App\Http\Requests\v1\DentalChartEntry\GetAllDentalChartEntriesRequest;
use App\Http\Requests\v1\DentalChartEntry\GetDentalChartEntryRequest;
use App\Http\Requests\v1\DentalChartEntry\StoreDentalChartEntryRequest;
use App\Http\Requests\v1\DentalChartEntry\UpdateDentalChartEntryRequest;
use App\Http\Resources\v1\DentalChartEntryResource;
use App\Models\DentalChartEntry;
use Illuminate\Http\JsonResponse;

class DentalChartEntryController extends Controller
{
    public function __construct(
        private readonly DentalChartEntryRepository $repository,
        private readonly CreateDentalChartEntryAction $createAction,
        private readonly UpdateDentalChartEntryAction $updateAction,
        private readonly DeleteDentalChartEntryAction $deleteAction
    ) {}

    public function index(GetAllDentalChartEntriesRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), DentalChartEntryResource::class);
        return $this->successResponse($result, 'Dental chart entries index compiled.');
    }

    public function show(GetDentalChartEntryRequest $request, DentalChartEntry $dentalChartEntry): JsonResponse
    {
        return $this->successResponse(
            new DentalChartEntryResource($dentalChartEntry->load('condition')),
            'Dental chart entry details fetched successfully.'
        );
    }

    public function store(StoreDentalChartEntryRequest $request): JsonResponse
    {
        try {
            $entry = $this->createAction->execute(
                DentalChartEntryMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new DentalChartEntryResource($entry->load('condition')),
                'Dental chart entry logged successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateDentalChartEntryRequest $request, DentalChartEntry $dentalChartEntry): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $dentalChartEntry,
                DentalChartEntryMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new DentalChartEntryResource($updated->load('condition')),
                'Dental chart entry updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteDentalChartEntryRequest $request, DentalChartEntry $dentalChartEntry): JsonResponse
    {
        $this->deleteAction->execute($dentalChartEntry);
        return $this->successResponse(null, 'Dental chart entry deleted successfully.');
    }
}
