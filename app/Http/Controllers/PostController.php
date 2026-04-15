<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class PostController extends Controller
{
    private function currentUserId(): int
    {
        return (int) (Auth::guard('sanctum')->id() ?? Auth::id() ?? 0);
    }

    public function index()
    {
        $likedByUserId = $this->currentUserId();

        $posts = Post::with('user')
            ->withCount([
                'likes',
                'comments',
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $likedByUserId),
            ])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($post) {
                $post->liked_by_me = (bool) $post->liked_by_me;
            });

        return response()->json($posts);
    }

    public function profilePosts()
    {
        $likedByUserId = $this->currentUserId();

        $posts = Post::where('user_id', Auth::id())
            ->withCount([
                'likes',
                'comments',
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $likedByUserId),
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($post) {
                $post->liked_by_me = (bool) $post->liked_by_me;
            });

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        Post::create([
            'user_id' => $request->user()->id,
            'body' => $request->body,
            'status' => 1,
        ]);

        return response()->json([
            'message' => 'Post created successfully'
        ], 201);
    }

    public function show($id)
    {
        $likedByUserId = $this->currentUserId();

        $post = Post::with('user')
            ->withCount([
                'likes',
                'comments',
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $likedByUserId),
            ])
            ->findOrFail($id);

        $post->liked_by_me = (bool) $post->liked_by_me;

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    public function userPosts($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $likedByUserId = $this->currentUserId();

        $posts = $user->posts()
            ->withCount([
                'likes',
                'comments',
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $likedByUserId),
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($post) {
                $post->liked_by_me = (bool) $post->liked_by_me;
            });

        return response()->json($posts);
    }

}
