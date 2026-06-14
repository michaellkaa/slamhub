<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private function generateVerificationCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function register(Request $request)
    {
        $validated = $request->validate(
            [
                'username' => 'required|string|max:50|unique:users,username',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ],
            [
                'username.unique' => 'Toto uživatelské jméno je již zabrané.',
                'email.unique' => 'Tento email je již použitý.',
                'password.min' => 'Heslo musí mít alespoň 8 znaků.',
                'password.confirmed' => 'Hesla se neshodují.',
            ]
        );

        $verificationCode = $this->generateVerificationCode();

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(
                $validated['password'] . config('app.password_pepper')
            ),
            'role' => 'user',
            'profile_pic' => 'profile_pics/default-avatar.png',
            'email_verification_code' => $verificationCode,
            'email_verification_code_sent_at' => now(),
        ]);

        try {
            $user->notify(new EmailVerificationCodeNotification($verificationCode));
        } catch (\Exception $e) {
            // Log email error but continue - user can still verify
            \Log::warning('Email verification code could not be sent', ['email' => $user->email, 'error' => $e->getMessage()]);
        }

        $response = [
            'message' => 'Registrace proběhla úspěšně. Na váš e-mail byl zaslán ověřovací kód.',
            'email' => $user->email,
        ];

        // In development, include code for testing purposes
        if (env('APP_ENV') === 'local') {
            $response['code'] = $verificationCode;
            $response['message'] .= ' [DEV MODE: Kód je ' . $verificationCode . ']';
        }

        return response()->json($response, 201);
    }

    /*public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Nesprávný email nebo heslo'
            ], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Email není ověřen. Zkontrolujte poštu a ověřte svůj účet pomocí kódu.',
            ], 403);
        }

        if ($user->is_banned) {
            return response()->json([
                'message' => 'Účet je zablokován'
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->update([
            'last_login_at' => now(),
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }*/


public function login(Request $request)
{
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $data['email'])->first();

    if (!$user) {
        return response()->json([
            'message' => 'Nesprávný email nebo heslo'
        ], 401);
    }
    
    $plainPassword = $data['password'];
    
    $valid =
        Hash::check(
            $plainPassword . config('app.password_pepper'),
            $user->password
        ) ||
        Hash::check(
            $plainPassword,
            $user->password
        );
    
    if (!$valid) {
        return response()->json([
            'message' => 'Nesprávný email nebo heslo'
        ], 401);
    }

    if (is_null($user->email_verification_code)) {
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }
    } elseif (!$user->email_verified_at) {
        return response()->json([
            'message' => 'Email není ověřen. Zkontrolujte poštu a ověřte svůj účet pomocí kódu.',
        ], 403);
    }

    if ($user->is_banned) {
        return response()->json([
            'message' => 'Účet je zablokován'
        ], 403);
    }

    $user->tokens()->delete();
    $token = $user->createToken('auth_token')->plainTextToken;

    $user->update([
        'last_login_at' => now(),
    ]);

    return response()->json([
        'message' => 'Logged in',
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user,
    ]);
}

public function logout(Request $request)
{
    if ($request->user()?->currentAccessToken()) {
        $request->user()->currentAccessToken()->delete();
    }

    return response()->json([
        'message' => 'Logged out'
    ]);
}

        public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email je již ověřen.',
            ], 200);
        }

        if ($user->email_verification_code !== $validated['code']) {
            return response()->json([
                'message' => 'Neplatný ověřovací kód.',
            ], 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_code_sent_at' => null,
        ]);

        return response()->json([
            'message' => 'Email byl úspěšně ověřen.',
        ]);
    }

    public function resendVerificationCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email je již ověřen.',
            ], 400);
        }

        $verificationCode = $this->generateVerificationCode();

        $user->update([
            'email_verification_code' => $verificationCode,
            'email_verification_code_sent_at' => now(),
        ]);

        $user->notify(new EmailVerificationCodeNotification($verificationCode));

        return response()->json([
            'message' => 'Ověřovací kód byl znovu odeslán na váš email.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
