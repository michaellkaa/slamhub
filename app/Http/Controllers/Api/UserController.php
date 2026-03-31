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
        'followers_count' => $user->followers()->count(),
        'following_count' => $user->following()->count(),
    ]);
}

    public function show($username)
{
    $user = User::where('username', $username)->firstOrFail();

    $isFollowing = false;

    if (auth()->check()) {
        $isFollowing = auth()->user()
            ->following()
            ->where('following_id', $user->id)
            ->exists();
    }

    return response()->json([
        'id' => $user->id,
        'username' => $user->username,
        'name' => $user->name,
        'role' => $user->role,
        'profile_pic_url' => $user->profile_pic_url,
        'followers_count' => $user->followers()->count(),
        'following_count' => $user->following()->count(),
        'is_following' => $isFollowing
    ]);
}

    public function index(Request $request)
    {
        $currentUser = $request->user();
    
        $users = User::where('id', '!=', $currentUser->id)->get();
    
        return response()->json($users);
    }
}
