<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedFields = $request->validated();
        $user = User::create($validatedFields);
        $token = $user->createToken($user->email)->plainTextToken;
        $user->save();
        $user->refresh();

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ], 201);
    }
}
