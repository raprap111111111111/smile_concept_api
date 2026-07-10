<?php

namespace App\Http\Controllers\v1;

use App\Domain\Role\Actions\CreateRoleAction;
use App\Domain\Role\Actions\DeleteRoleAction;
use App\Domain\Role\Actions\UpdateRoleAction;
use App\Domain\Role\Mappers\RoleMapper;
use App\Domain\Role\Repositories\RoleRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Role\GetAllRoleRequest;
use App\Http\Requests\v1\Role\StoreRoleRequest;
use App\Http\Requests\v1\Role\UpdateRoleRequest;
use App\Http\Resources\v1\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleRepository $repository,
        private readonly CreateRoleAction $createAction,
        private readonly UpdateRoleAction $updateAction,
        private readonly DeleteRoleAction $deleteAction
    ) {}

    public function index(GetAllRoleRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), RoleResource::class);
        return $this->responseSuccess($result, 'Roles retrieved successfully');
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return $this->responseSuccess(
            new RoleResource($role),
            'Role found successfully'
        );
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->createAction->execute(
            RoleMapper::fromCreateRequest($request)
        );

        return $this->responseSuccess(
            new RoleResource($role),
            'Role created successfully',
            JsonResponse::HTTP_CREATED
        );
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $updatedRole = $this->updateAction->execute(
            $role,
            RoleMapper::fromUpdateRequest($request)
        );

        return $this->responseSuccess(
            new RoleResource($updatedRole),
            'Role updated successfully'
        );
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->deleteAction->execute($role);
        return $this->responseSuccess(null, 'Role deleted successfully');
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        $role->load('permissions');

        return $this->responseSuccess(
            new RoleResource($role),
            'Role permissions synced successfully'
        );
    }

    public function assignPermission(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->givePermissionTo($validated['permissions']);
        $role->load('permissions');

        return $this->responseSuccess(
            new RoleResource($role),
            'Permission assigned successfully'
        );
    }

    public function removePermission(Role $role, Permission $permission): JsonResponse
    {
        $role->revokePermissionTo($permission->name);
        $role->load('permissions');

        return $this->responseSuccess(
            new RoleResource($role),
            'Permission removed successfully'
        );
    }
}