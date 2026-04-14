<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirect()
    {
        $callbackUrl = config('services.google.redirect') ?: url('/api/auth/google/callback');

        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl($callbackUrl)
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback()
    {
        $callbackUrl = config('services.google.redirect') ?: url('/api/auth/google/callback');

        $googleUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl($callbackUrl)
            ->user();

        $email = $googleUser->getEmail();
        if (!$email) {
            return redirect()->to(url('/auth/google/callback') . '?error=' . urlencode('Google account does not provide an email.'));
        }
        $name = $googleUser->getName() ?: 'Uživatel';

        $user = User::where('email', $email)->first();

        if (!$user) {
            $base = Str::slug(Str::before($email, '@')) ?: Str::slug($name) ?: 'user';
            $username = $base;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $i++;
                $username = $base . $i;
            }

            $user = User::create([
                'email' => $email,
                'name' => $name,
                'username' => $username,
                'password' => null, // Google login
                'role' => 'user',
                'google_id' => $googleUser->getId(),
            ]);
        } else {
            if (!$user->google_id) {
                $user->google_id = $googleUser->getId();
                $user->save();
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect()->to(url('/auth/google/callback') . '?access_token=' . urlencode($token));
    }
}
