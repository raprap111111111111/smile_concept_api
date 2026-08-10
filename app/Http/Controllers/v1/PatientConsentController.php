<?php

namespace App\Http\Controllers\v1;

use App\Domain\Consents\Actions\GenerateConsentPdfAction;
use App\Domain\Consents\Actions\SignConsentAction;
use App\Domain\Consents\Actions\VoidConsentAction;
use App\Domain\Consents\Mappers\ConsentMapper;
use App\Domain\Consents\Repositories\PatientConsentRepository;
use App\Http\Requests\v1\Consent\SignConsentRequest;    
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Consent\GetAllPatientConsentsRequest;
use App\Http\Requests\v1\Consent\VoidConsentRequest;
use App\Http\Resources\v1\PatientConsentResource;
use App\Models\Appointment;
use App\Models\PatientConsent;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PatientConsentController extends Controller
{
    public function __construct(
        private readonly PatientConsentRepository $repository,
        private readonly SignConsentAction         $signAction,
        private readonly VoidConsentAction         $voidAction,
        private readonly GenerateConsentPdfAction  $pdfAction,
    ) {}

    public function index(GetAllPatientConsentsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate(
            $request->validated(),
            PatientConsentResource::class
        );

        return $this->successResponse($result, 'Signed patient consents retrieved.');
    }

    public function show(PatientConsent $consent): JsonResponse
    {
        $this->authorize('view', $consent);

        return $this->successResponse(
            new PatientConsentResource(
                $consent->load(['template', 'patient', 'appointment', 'signedByStaff'])
            ),
            'Patient consent record retrieved.'
        );
    }

    public function sign(SignConsentRequest $request): JsonResponse
    {
        try {
            $consent = $this->signAction->execute(
                ConsentMapper::fromSignRequest($request)
            );

            return $this->successResponse(
                new PatientConsentResource(
                    $consent->load(['template', 'patient', 'signedByStaff'])
                ),
                'Legal patient consent successfully signed.',
                Response::HTTP_CREATED
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function void(VoidConsentRequest $request, PatientConsent $consent): JsonResponse
    {
        try {
            $voided = $this->voidAction->execute(
                $consent,
                ConsentMapper::fromVoidRequest($request)   // ✅ added below
            );

            return $this->successResponse(
                new PatientConsentResource($voided->load(['template', 'patient'])),
                'Consent record voided.'
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function pdf(PatientConsent $consent)
    {
        $this->authorize('print', $consent);

        return $this->pdfAction->execute($consent)->stream("consent-{$consent->id}.pdf");
    }

    public function download(PatientConsent $consent)
    {
        $this->authorize('print', $consent);

        return $this->pdfAction->execute($consent)->download("consent-{$consent->id}.pdf");
    }

    public function byAppointment(Appointment $appointment): JsonResponse
    {
        $this->authorize('viewAny', PatientConsent::class);

        $consents = $this->repository->forAppointment($appointment->id);

        return $this->successResponse(
            PatientConsentResource::collection($consents),
            'Appointment consent records retrieved.'
        );
    }
}