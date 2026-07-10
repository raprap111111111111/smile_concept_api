<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\Actions\CreateServiceAction;
use App\Domain\Services\Actions\DeleteServiceAction;
use App\Domain\Services\Actions\GetServicesAction;
use App\Domain\Services\Actions\UpdateServiceAction;
use App\Domain\Services\DTOs\ServiceData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StoreServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private readonly GetServicesAction $getServicesAction,
        private readonly CreateServiceAction $createServiceAction,
        private readonly UpdateServiceAction $updateServiceAction,
        private readonly DeleteServiceAction $deleteServiceAction,
    ) {}

    public function publicIndex(Request $request): JsonResponse
    {
        $services = $this->getServicesAction->execute(
            filters: [
                'active_only' => true,
                'featured_only' => $request->boolean('featured_only'),
                'category' => $request->category,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $services = $this->getServicesAction->execute(
            filters: [
                'search' => $request->search,
                'category' => $request->category,
            ],
            paginate: true,
            perPage: $request->per_page ?? 15
        );

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->createServiceAction->execute(
            ServiceData::fromArray($request->validated() + ['image' => $request->file('image')])
        );

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => $service,
        ], 201);
    }

    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $updated = $this->updateServiceAction->execute(
            $service,
            ServiceData::fromArray($request->validated() + ['image' => $request->file('image')])
        );

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => $updated,
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->deleteServiceAction->execute($service);

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully',
        ]);
    }
}
