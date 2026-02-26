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

        $isFollowing = false;

        if ($authUser) {
            $isFollowing = $authUser->following()
                ->where('following_id', $user->id)
                ->exists();
        }

        return response()->json([
            ...$user->toArray(),
            'is_following' => $isFollowing,
        ]);
    }
}
