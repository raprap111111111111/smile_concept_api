<?php

namespace App\Http\Controllers\v1;

use App\Domain\Branch\Actions\CreateBranchAction;
use App\Domain\Branch\Actions\DeleteBranchAction;
use App\Domain\Branch\Actions\UpdateBranchAction;
use App\Domain\Branch\Mappers\BranchMapper;
use App\Domain\Branch\Repositories\BranchRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Branch\GetAllBranchRequest;
use App\Http\Requests\v1\Branch\GetBranchRequest;
use App\Http\Requests\v1\Branch\StoreBranchRequest;
use App\Http\Requests\v1\Branch\UpdateBranchRequest;
use App\Http\Resources\v1\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;

class BranchController extends Controller
{
    
    public function __construct(
        private readonly BranchRepository $repository,
        private readonly CreateBranchAction $createAction,
        private readonly UpdateBranchAction $updateAction,
        private readonly DeleteBranchAction $deleteAction
    ) {}

    public function index(GetAllBranchRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), BranchResource::class);
        return $this->responseSuccess($result, 'Branches retrieved successfully');
    }
    
    public function show(GetBranchRequest $request, Branch $branch): JsonResponse
    {
        // 2. Load relationships and return
        return $this->responseSuccess(
            new BranchResource($branch->load([])),
            'Branch found successfully'
        );
    }

    public function store(StoreBranchRequest $request): JsonResponse
    {
        // Use the Mapper to convert Request → DTO
        $branch = $this->createAction->execute(
            BranchMapper::fromCreateRequest($request)
        );

        return $this->responseSuccess(
            new BranchResource($branch),
            'Branch created successfully',
            JsonResponse::HTTP_CREATED
        );
    }

    public function update(UpdateBranchRequest $request, Branch $branch): JsonResponse
    {
        // Use the Mapper to convert Request → DTO
        $updatedBranch = $this->updateAction->execute(
            $branch,
            BranchMapper::fromUpdateRequest($request)
        );

        return $this->responseSuccess(
            new BranchResource($updatedBranch),
            'Branch updated successfully'
        );
    }

    public function destroy(Branch $branch): JsonResponse
    {
        // Now you are passing the Model instance (which the Action now expects)
        $this->deleteAction->execute($branch);

        return $this->responseSuccess(null, 'Branch deleted successfully.');
    }
}
