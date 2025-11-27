<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class GoogleController extends Controller
{
    // Přesměrování na Google login
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // Callback z Google po úspěšném přihlášení
    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Najdi uživatele podle emailu nebo vytvoř nového
        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'password' => null,       // nemá heslo
                'role' => 'user',
                'google_id' => $googleUser->getId(),
            ]
        );

        // Vytvoření Sanctum tokenu pro SPA
        $token = $user->createToken('auth_token')->plainTextToken;

        // Vrátíme token a info uživatele (JSON) – SPA může uložit token
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}
