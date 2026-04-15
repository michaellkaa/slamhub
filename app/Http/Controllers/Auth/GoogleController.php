<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Throwable;

class GoogleController extends Controller
{
    public function redirect()
    {
        $callbackUrl = config('services.google.redirect') ?: url('/api/auth/google/callback');
        $verifySsl = filter_var(config('services.google.verify_ssl', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $verifySsl = $verifySsl ?? true;

        $driver = Socialite::driver('google')
            ->stateless()
            ->redirectUrl($callbackUrl)
            ->scopes(['openid', 'profile', 'email']);

        $driver->setHttpClient(new Client(['verify' => $verifySsl]));

        return $driver->redirect();
    }

    public function callback()
    {
        $callbackUrl = config('services.google.redirect') ?: url('/api/auth/google/callback');
        $verifySsl = filter_var(config('services.google.verify_ssl', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $verifySsl = $verifySsl ?? true;

        try {
            $driver = Socialite::driver('google')
                ->stateless()
                ->redirectUrl($callbackUrl);

            $driver->setHttpClient(new Client(['verify' => $verifySsl]));
            $googleUser = $driver->user();
        } catch (Throwable $e) {
            $isLocal = app()->environment('local');
            $isCurlCertError = str_contains($e->getMessage(), 'cURL error 60');

            if ($isLocal && $isCurlCertError && $verifySsl) {
                try {
                    $retryDriver = Socialite::driver('google')
                        ->stateless()
                        ->redirectUrl($callbackUrl);

                    $retryDriver->setHttpClient(new Client(['verify' => false]));
                    $googleUser = $retryDriver->user();
                } catch (Throwable $retryException) {
                    Log::error('Google OAuth callback retry failed', [
                        'message' => $retryException->getMessage(),
                        'type' => get_class($retryException),
                    ]);

                    return redirect()->to(
                        url('/auth/google/callback') . '?error=' . urlencode('Google přihlášení selhalo. Zkus to prosím znovu.')
                    );
                }
            } else {
            Log::error('Google OAuth callback failed', [
                'message' => $e->getMessage(),
                'type' => get_class($e),
            ]);

            return redirect()->to(
                url('/auth/google/callback') . '?error=' . urlencode('Google přihlášení selhalo. Zkus to prosím znovu.')
            );
            }
        }

        $email = $googleUser->getEmail();
        if (!$email) {
            return redirect()->to(url('/auth/google/callback') . '?error=' . urlencode('Google account does not provide an email.'));
        }
        $name = $googleUser->getName() ?: 'Uživatel';
        $googleId = $googleUser->getId();

        $user = User::where('email', $email)->first();

        if (!$user) {
            $base = Str::slug(Str::before($email, '@')) ?: Str::slug($name) ?: 'user';
            $username = $base;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $i++;
                $username = $base . $i;
            }

            $attributes = [
                'email' => $email,
                'name' => $name,
                'password' => null, // Google login
            ];

            if (Schema::hasColumn('users', 'username')) {
                $attributes['username'] = $username;
            }
            if (Schema::hasColumn('users', 'role')) {
                $attributes['role'] = 'user';
            }
            if (Schema::hasColumn('users', 'google_id')) {
                $attributes['google_id'] = $googleId;
            }
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $attributes['email_verified_at'] = now();
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $attributes['last_login_at'] = now();
            }

            $user = User::create($attributes);
        } else {
            if (Schema::hasColumn('users', 'is_banned') && $user->is_banned) {
                return redirect()->to(url('/auth/google/callback') . '?error=' . urlencode('Účet je zablokován.'));
            }

            $updates = [];
            if (Schema::hasColumn('users', 'google_id') && !$user->google_id) {
                $updates['google_id'] = $googleId;
            }
            if (Schema::hasColumn('users', 'email_verified_at') && !$user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $updates['last_login_at'] = now();
            }

            if (!empty($updates)) {
                $user->update($updates);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect()->to(url('/auth/google/callback') . '?access_token=' . urlencode($token));
    }
}
