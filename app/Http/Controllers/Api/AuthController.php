<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\ProfileResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login($request->input('email'), $request->input('password'));

        return response()->json([
            'token' => $user->createToken('spa')->accessToken,
            'user' => new ProfileResource($user),
        ]);
    }

    public function me(Request $request): ProfileResource
    {
        return new ProfileResource($request->user());
    }

    public function logout(Request $request): Response
    {
        $request->user()->token()->revoke();

        return response()->noContent();
    }
}
