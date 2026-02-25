<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FollowController extends Controller
{

    public function toggleFollow($username)
    {
        $authUser = Auth::user();
        $userToFollow = User::where('username', $username)->firstOrFail();

        if ($authUser->id === $userToFollow->id) {
            return response()->json(['message' => 'Nemůžeš sledovat sám sebe'], 400);
        }

        if (!in_array($userToFollow->role, ['performer', 'organizer'])) {
            return response()->json([
                'message' => 'Tento uživatel nemůže být sledován.'
            ], 403);
        }

        $isFollowing = $authUser->following()->where('following_id', $userToFollow->id)->exists();

        if ($isFollowing) {
            $authUser->following()->detach($userToFollow->id);
            return response()->json(['following' => false]);
        } else {
            $authUser->following()->attach($userToFollow->id);
            return response()->json(['following' => true]);
        }
    }

    public function followersList($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        if (!in_array($user->role, ['performer', 'organizer'])) {
            return response()->json([
                'message' => 'Tento uživatel nemá followers list.'
            ], 403);
        }

        $followers = $user->followers()->get(['id', 'username', 'role']);

        return response()->json($followers);
    }

    public function followingList($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $following = $user->following()->get(['id', 'username', 'role']);

        return response()->json($following);
    }
}