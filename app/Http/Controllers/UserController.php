<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show($username)
    {
        $user = User::where('username', $username)
            ->withCount(['followers', 'following'])
            ->firstOrFail();

        $authUser = auth()->user();

        $user->is_following = $authUser
            ? $authUser->following()->where('following_id', $user->id)->exists()
            : false;

        return response()->json($user);
    }
}