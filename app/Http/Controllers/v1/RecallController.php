<?php

namespace App\Http\Controllers\v1;

use App\Domain\Recalls\Actions\CreateRecallAction;
use App\Domain\Recalls\Actions\DeleteRecallAction;
use App\Domain\Recalls\Actions\UpdateRecallAction;
use App\Domain\Recalls\Mappers\RecallMapper;
use App\Domain\Recalls\Repositories\RecallRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Recall\DeleteRecallRequest;
use App\Http\Requests\v1\Recall\GetAllRecallsRequest;
use App\Http\Requests\v1\Recall\GetRecallRequest;
use App\Http\Requests\v1\Recall\StoreRecallRequest;
use App\Http\Requests\v1\Recall\UpdateRecallRequest;
use App\Http\Resources\v1\RecallResource;
use App\Models\Recall;
use App\Models\RecallType;
use App\Models\Appointment;
use App\Enums\RecallStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class RecallController extends Controller
{
    public function __construct(
        private readonly RecallRepository $repository,
        private readonly CreateRecallAction $createAction,
        private readonly UpdateRecallAction $updateAction,
        private readonly DeleteRecallAction $deleteAction
    ) {}

    public function index(GetAllRecallsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), RecallResource::class);
        return $this->successResponse($result, 'Patient active recalls log retrieved.');
    }

    public function show(GetRecallRequest $request, Recall $recall): JsonResponse
    {
        return $this->successResponse(
            new RecallResource($recall->load(['patient', 'recallType'])),
            'Patient recall record retrieved successfully.'
        );
    }

    public function store(StoreRecallRequest $request): JsonResponse
    {
        try {
            $recall = $this->createAction->execute(
                RecallMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new RecallResource($recall->load(['patient', 'recallType'])),
                'Patient cleaning recall created successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateRecallRequest $request, Recall $recall): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $recall,
                RecallMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new RecallResource($updated->load(['patient', 'recallType'])),
                'Patient recall profile modified successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteRecallRequest $request, Recall $recall): JsonResponse
    {
        $this->deleteAction->execute($recall);
        return $this->successResponse(null, 'Patient recall record dropped.');
    }

    /**
     * Generate dynamic hygiene recalls based on completed appointments
     */
    public function autoGenerateRecalls(): JsonResponse
    {
        // 1. Locate the standard "cleaning" recall configuration template dynamically
        $cleaningType = RecallType::where('slug', 'cleaning')->first();

        if (!$cleaningType) {
            return $this->errorResponse('Standard cleaning configuration template missing from system templates.', 404);
        }

        $recentAppointments = Appointment::where('status', 'confirmed')
            ->whereBetween('end_time', [now()->subDays(7), now()])
            ->get();

        $generatedCount = 0;

        foreach ($recentAppointments as $appointment) {
            $existing = Recall::where('user_id', $appointment->user_id)
                ->where('recall_type_id', $cleaningType->id)
                ->where('status', RecallStatus::PENDING)
                ->exists();

            if (!$existing) {
                Recall::create([
                    'user_id' => $appointment->user_id,
                    'recall_type_id' => $cleaningType->id,
                    'due_date' => Carbon::parse($appointment->end_time)->addMonths($cleaningType->frequency_months),
                    'status' => RecallStatus::PENDING,
                ]);
                $generatedCount++;
            }
        }

        return $this->successResponse([
            'generated_recalls_count' => $generatedCount
        ], 'Automated dynamic cleanings recalls generated.');
    }
}
