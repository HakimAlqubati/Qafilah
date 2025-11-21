<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Auth\AuthRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends ApiController
{
    public function __construct(
        protected AuthRepositoryInterface $authRepository
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = $this->authRepository->attemptLogin(
            $credentials['email'],
            $credentials['password']
        );

        if (! $user) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse([
            'user'       => new UserResource($user),
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 'Logged in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authRepository->logout($user);

        return $this->successResponse(null, 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            new UserResource($request->user()),
            'User data.'
        );
    }
}
