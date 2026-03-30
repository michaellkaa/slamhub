<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FollowController extends Controller
{

    public function toggleFollow($username)
    {
        $authUser = auth()->user();
        $userToFollow = User::where('username', $username)->firstOrFail();

        if ($authUser->id === $userToFollow->id) {
            return response()->json(['message' => 'Nemůžeš sledovat sám sebe'], 400);
        }

        if (!in_array($userToFollow->role, ['performer', 'organizer'])) {
            return response()->json(['message' => 'Tento uživatel nemůže být sledován.'], 403);
        }

        $isFollowing = $authUser->following()
            ->where('following_id', $userToFollow->id)
            ->exists();

        if ($isFollowing) {
            $authUser->following()->detach($userToFollow->id);
            $following = false;
        } else {
            $authUser->following()->attach($userToFollow->id);
            $following = true;
        }

        $followersCount = $userToFollow->followers()->count();

        return response()->json([
            'following' => $following,
            'followers_count' => $followersCount
        ]);
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

    public function followStatus($username)
    {
        $authUser = auth()->user();
        $user = User::where('username', $username)->firstOrFail();

        $isFollowing = false;
        if ($authUser && $authUser->id !== $user->id) {
            $isFollowing = $authUser->following()->where('following_id', $user->id)->exists();
        }

        return response()->json([
            'is_following' => $isFollowing
        ]);
    }
}