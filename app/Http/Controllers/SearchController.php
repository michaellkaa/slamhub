<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\Post;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $role = $request->query('role');

        if (!$q || strlen($q) < 2) {
            return response()->json([
                'users' => [],
                'events' => [],
                /*'posts' => [],*/
            ]);
        }
        
        $users = User::where('name', 'like', "%{$q}%")
            ->whereIn('role', ['organizer', 'performer'])
            ->limit(5)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role,
                'profile_pic' => $u->profile_pic
                    ? asset('storage/' . $u->profile_pic)
                    : null,
            ]);


        return response()->json([
            'users' => $users,

            'events' => Event::where('title', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'title']),
            /*'posts' => Post::where('body', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'body']),*/
        ]);
    }
}
