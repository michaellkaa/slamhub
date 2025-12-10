<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate( [
        //'username' => 'required|string|max:255|unique:users,username',
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ],
    [
        'username.unique' => 'Toto uživatelské jméno je již zabrané.',
        'email.unique' => 'Tento email je již použitý.',
        'password.min' => 'Heslo musí mít alespoň 8 znaků.',
        'password.confirmed' => 'Hesla se neshodují.',
    ]);

        $user = User::create([
            //'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'use',
        ]);

        return response()->json(['message' => 'Registrace proběhla úspěšně', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // login přes Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Odhlášení proběhlo úspěšně']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
