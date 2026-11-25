<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');

        $users = User::query()
            ->with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return UserResource::collection($users);
    }

    public function store(UserRequest $request): UserResource
    {
        $user = $this->userService->create($request->validated());
        $user->load('roles');

        return new UserResource($user);
    }

    public function update(UserRequest $request, User $user): UserResource
    {
        $user = $this->userService->update($user, $request->validated(), $request->user());
        $user->load('roles');

        return new UserResource($user);
    }
}
