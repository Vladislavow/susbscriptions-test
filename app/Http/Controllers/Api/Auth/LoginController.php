<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validatedFields = $request->validated();
        $user = User::where('email', $validatedFields['email'])->first();

        if (!$user || !Hash::check($validatedFields['password'], $user->password)) {
            return response()->json(__('responses.invalid_credentials'), Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken($validatedFields['email'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->tokens()->delete();

        return response()->json(__('responses.logged_out'));
    }

}
