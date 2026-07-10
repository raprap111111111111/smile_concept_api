<?php

namespace App\Http\Controllers\v1;

use App\Domain\Consents\Actions\SignConsentAction;
use App\Domain\Consents\Mappers\ConsentMapper;
use App\Domain\Consents\Repositories\ConsentTemplateRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Consent\SignConsentRequest;
use App\Http\Resources\v1\PatientConsentResource;
use App\Models\ConsentTemplate;
use Illuminate\Http\JsonResponse;

class ConsentController extends Controller
{
    public function __construct(
        private readonly ConsentTemplateRepository $templateRepository,
        private readonly SignConsentAction $signAction
    ) {}

    public function templates(): JsonResponse
    {
        return $this->successResponse($this->templateRepository->all(), 'Clinical consent forms index retrieved.');
    }

    public function sign(SignConsentRequest $request): JsonResponse
    {
        try {
            $signedRecord = $this->signAction->execute(
                ConsentMapper::fromSignRequest($request)
            );

            return $this->successResponse(
                new PatientConsentResource($signedRecord->load('template')),
                'Legal patient consent document successfully signed.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
