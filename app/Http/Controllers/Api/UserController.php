<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'role' => $user->role,
            'profile_pic_url' => $user->profile_pic_url,
        ]);
    }

    public function show($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'role' => $user->role,
            'profile_pic_url' => $user->profile_pic_url,
        ]);
    }
}
