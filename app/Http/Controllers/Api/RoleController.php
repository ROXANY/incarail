<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService) {}

    public function index(): AnonymousResourceCollection
    {
        $roles = Role::query()
            ->where('guard_name', 'api')
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return RoleResource::collection($roles);
    }

    public function store(RoleRequest $request): RoleResource
    {
        $role = $this->roleService->create($request->validated());
        $role->load('permissions');

        return new RoleResource($role);
    }

    public function update(RoleRequest $request, Role $role): RoleResource
    {
        $role = $this->roleService->update($role, $request->validated());
        $role->load('permissions');

        return new RoleResource($role);
    }

    public function destroy(Role $role): Response
    {
        $this->roleService->delete($role);

        return response()->noContent();
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::where('guard_name', 'api')->orderBy('name')->pluck('name');

        return response()->json(['data' => $permissions]);
    }
}
