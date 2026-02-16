<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show($username)
    {
        $user = User::where('username', $username)
            ->select('id', 'name', 'username', 'role', 'profile_pic')
            ->firstOrFail();

        return response()->json($user);
    }
}
