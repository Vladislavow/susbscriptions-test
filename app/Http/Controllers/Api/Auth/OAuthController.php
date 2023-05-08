<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function getGoogleRedirect(Request $request): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleAuth(): RedirectResponse
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $ex) {
            return redirect('login');
        }

        $existsUser = User::where('email', $user->email)->first();

        if ($existsUser) {
            auth()->login($user, true);
        } else {
            $newUser = new User();
            $newUser->name = $user->name;
            $newUser->email = $user->email;
            $newUser->password = Str::random(15);

            auth()->login($newUser, true);
        }

        return redirect()->to('/');
    }
}
