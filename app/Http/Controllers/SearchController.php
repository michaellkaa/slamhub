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

        if (!$q || strlen($q) < 2) {
            return response()->json([
                'users' => [],
                'events' => [],
                /*'posts' => [],*/
            ]);
        }

        return response()->json([
            'users' => User::where('name', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'name']),

            'events' => Event::where('title', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'title', 'starts_at']),

            /*'posts' => Post::where('body', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'body']),*/
        ]);
    }
}
