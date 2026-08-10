<?php

namespace App\Http\Controllers\v1;

use App\Domain\Consents\Repositories\ConsentTemplateRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ConsentTemplateResource;
use App\Models\ConsentTemplate;
use Illuminate\Http\JsonResponse;

class ConsentTemplateController extends Controller
{
    public function __construct(
        private readonly ConsentTemplateRepository $repository
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ConsentTemplate::class);

        return $this->successResponse(
            ConsentTemplateResource::collection($this->repository->activeOnly()),
            'Clinical consent form templates retrieved.'
        );
    }

    public function show(ConsentTemplate $template): JsonResponse
    {
        $this->authorize('view', $template);

        return $this->successResponse(
            new ConsentTemplateResource($template),
            'Consent template retrieved.'
        );
    }
}