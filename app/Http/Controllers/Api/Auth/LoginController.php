<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Cart;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\order\CartRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends ApiController
{
    public function __construct(
        protected AuthRepositoryInterface $authRepository,
        protected  CartRepository $cartRepository
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

        if ($fcmToken = $request->header('x-device-token')) {
            $user->fcm_token = $fcmToken;
            $user->save();
        }

        if (($credentials['is_guest'] ?? false) === true) {
            $guestCart = Cart::where('cart_token', $request->header('X-Cart-Token'))
                ->where('status', 'active')
                ->latest('id')
                ->lockForUpdate()
                ->first();
            if($guestCart){
                $this->cartRepository
                    ->forBuyer((int) $user->id)
                    ->withClaimToken($request->header('X-Cart-Token'))
                    ->claimGuestCart();
            }



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

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = \App\Models\User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'phone'    => $request->phone,
        ]);

        if ($fcmToken = $request->header('x-device-token')) {
            $user->fcm_token = $fcmToken;
            $user->save();
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse([
            'user'       => new UserResource($user),
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            new UserResource($request->user()),
            'User data.'
        );
    }
}
